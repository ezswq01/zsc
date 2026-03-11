<?php

namespace App\Http\Controllers;

use App\Models\DeviceStatus;
use App\Models\Device;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DeviceStatusController extends Controller
{
    public function get_device_status($id)
    {
        $deviceStatus = DeviceStatus::with(['device'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $deviceStatus,
        ]);
    }

    public function notes(Request $request)
    {
        if (!$request->notes) {
            return response()->json([
                'success' => false,
                'message' => 'Notes is required.',
            ], 500);
        }

        if ($request->notes === 'Normal State') {
            return response()->json([
                'success' => false,
                'message' => 'Notes cannot be Normal State!',
            ], 500);
        }

        $device_status  = DeviceStatus::find($request->device_status_id);
        $device_id      = $device_status->device_id;
        $status_type_id = $device_status->status_type_id;

        $normal_state_device_status = DeviceStatus::where('device_id', $device_id)
            ->where('status_type_id', $status_type_id)
            ->where('notes', 'Normal State')
            ->where('created_at', '>', $device_status->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($normal_state_device_status) {
            $device_status->notes          = $request->notes;
            $device_status->marked_as_read = true;
            $device_status->noted          = true;
            $device_status->user_id        = auth()->user()->id;
            $device_status->save();

            DeviceStatus::where('device_id', $device_id)
                ->where('status_type_id', $status_type_id)
                ->where('notes', '!=', 'Normal State')
                ->update(['marked_as_read' => true]);
        } else {
            $device_status->notes          = $request->notes;
            $device_status->marked_as_read = false;
            $device_status->noted          = true;
            $device_status->user_id        = auth()->user()->id;
            $device_status->save();
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Notes updated successfully.',
            'device_status' => $device_status->load('status_type.status_type_widget'),
        ]);
    }

    /**
     * Display the device statuses view and handle AJAX requests for DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DeviceStatus::with(['device', 'user'])->select('device_status.*');

            // Date range filter
            if ($request->filled('date')) {
                $dates = explode(' - ', $request->date);
                if (count($dates) == 2) {
                    $start = Carbon::parse($dates[0])->startOfDay();
                    $end   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('device_status.created_at', [$start, $end]);
                }
            }

            // Location and Sub-location filters
            $query->whereHas('device', function ($subQuery) use ($request) {
                if ($request->filled('branch')) {
                    $subQuery->where('branch', $request->branch);
                }
                if ($request->filled('building')) {
                    $subQuery->where('building', $request->building);
                }
            });

            // Global search handling
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('notes', 'ILIKE', "%{$search}%")
                      ->orWhereHas('device', function ($subQuery) use ($search) {
                          $subQuery->where('device_id', 'ILIKE', "%{$search}%")
                                   ->orWhere('branch', 'ILIKE', "%{$search}%")
                                   ->orWhere('building', 'ILIKE', "%{$search}%");
                      })
                      ->orWhereHas('user', function ($subQuery) use ($search) {
                          $subQuery->where('name', 'ILIKE', "%{$search}%");
                      });
                });
            }

            return DataTables::of($query)
                ->addColumn('user_name', function ($model) {
                    return $model->user->name ?? '-';
                })
                ->rawColumns(['marked_as_read'])
                ->make(true);
        }

        $branches  = Device::select('branch')->whereNotNull('branch')->distinct()->orderBy('branch')->get();
        $buildings = Device::select('branch', 'building')->whereNotNull('building')->distinct()->orderBy('branch')->orderBy('building')->get();

        return view('admin.device_statuses.index', compact('branches', 'buildings'));
    }

    /**
     * Export device statuses to a CSV file respecting all active filters.
     */
    public function export(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $filename = 'device_statuses_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Time', 'Device ID', 'Location', 'Sub Location', 'Notes', 'Updated By', 'Last Updated']);

            $query->chunk(2000, function ($statuses) use ($handle) {
                foreach ($statuses as $status) {
                    fputcsv($handle, [
                        $status->created_at?->format('Y-m-d H:i:s'),
                        $status->device?->device_id,
                        $status->device?->branch,
                        $status->device?->building,
                        $status->notes,
                        $status->user?->name,
                        $status->updated_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export device statuses to an Excel (SpreadsheetML .xls) file respecting all active filters.
     * Uses Excel 2003 XML format — no additional packages required.
     */
    public function exportExcel(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $filename = 'device_statuses_' . now()->format('Ymd_His') . '.xls';
        $headers  = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columnHeaders = ['Time', 'Device ID', 'Location', 'Sub Location', 'Notes', 'Updated By', 'Last Updated'];

        $callback = function () use ($query, $columnHeaders) {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
            echo '<Styles><Style ss:ID="header"><Font ss:Bold="1"/></Style></Styles>' . "\n";
            echo '<Worksheet ss:Name="Device Statuses"><Table>' . "\n";

            // Header row
            echo '<Row>';
            foreach ($columnHeaders as $h) {
                echo '<Cell ss:StyleID="header"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            }
            echo '</Row>' . "\n";

            // Data rows
            $query->chunk(2000, function ($statuses) {
                foreach ($statuses as $status) {
                    $cells = [
                        $status->created_at?->format('Y-m-d H:i:s') ?? '',
                        $status->device?->device_id ?? '',
                        $status->device?->branch ?? '',
                        $status->device?->building ?? '',
                        $status->notes ?? '',
                        $status->user?->name ?? '',
                        $status->updated_at?->format('Y-m-d H:i:s') ?? '',
                    ];

                    echo '<Row>';
                    foreach ($cells as $cell) {
                        echo '<Cell><Data ss:Type="String">' . htmlspecialchars((string) $cell, ENT_XML1, 'UTF-8') . '</Data></Cell>';
                    }
                    echo '</Row>' . "\n";
                }
            });

            echo '</Table></Worksheet></Workbook>';
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Build the shared filtered query used by both export() and exportExcel().
     */
    private function buildExportQuery(Request $request)
    {
        $query = DeviceStatus::with(['device', 'user'])->select('device_status.*');

        // Date filter
        if ($request->filled('date')) {
            $dates = explode(' - ', $request->date);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end   = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('device_status.created_at', [$start, $end]);
            }
        }

        // Location filters
        $query->whereHas('device', function ($subQuery) use ($request) {
            if ($request->filled('branch')) {
                $subQuery->where('branch', $request->branch);
            }
            if ($request->filled('building')) {
                $subQuery->where('building', $request->building);
            }
        });

        // Global search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'ILIKE', "%{$search}%")
                  ->orWhereHas('device', function ($subQuery) use ($search) {
                      $subQuery->where('device_id', 'ILIKE', "%{$search}%")
                               ->orWhere('branch', 'ILIKE', "%{$search}%")
                               ->orWhere('building', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortCol = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortCol, $sortDir);

        return $query;
    }
}
