<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatusType\StoreStatusTypeRequest;
use App\Http\Requests\StatusType\UpdateStatusTypeRequest;
use App\Models\Device;
use App\Models\DeviceStatus;
use App\Models\StatusType;
use App\Models\StatusTypeWidget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class StatusTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:status-types-create')->only(['create', 'store']);
        $this->middleware('can:status-types-read')->only(['index', 'show']);
        $this->middleware('can:status-types-update')->only(['edit', 'update']);
        $this->middleware('can:status-types-delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            return DataTables::of(StatusType::query())
                ->addIndexColumn()
                ->editColumn('name', function ($model) {
                    return '<a href="' . route('admin.status_types.show', $model->id) . '">' . $model->name . '</a>';
                })
                ->addColumn('options', 'admin.status_types.datatables.options')
                ->setRowAttr([
                    'data-model-id' => function ($model) {
                        return $model->id;
                    }
                ])
                ->rawColumns(['name', 'options'])
                ->toJson();
        }

        return view('admin.status_types.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.status_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStatusTypeRequest $request)
    {
        $validated = $request->all();

        StatusType::create($validated);
        return redirect()->route('admin.status_types.index')
            ->with('success', 'Device Type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = StatusType::findOrFail($id);
        return view('admin.status_types.show', compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = StatusType::findOrFail($id);
        return view('admin.status_types.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStatusTypeRequest $request, $id)
    {
        $validated = $request->all();

        StatusType::find($id)->update($validated);

        return redirect()->route('admin.status_types.index')
            ->with('success', 'Device Type updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        StatusType::find($id)->delete();
        return redirect()->route('admin.status_types.index')
            ->with('success', 'Device Type deleted successfully');
    }

    /**
     * Display the history of device statuses for a given status type.
     * Handles both the initial page load and server-side AJAX requests for DataTables.
     *
     * @param  int  $id  The status_type_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function history($id, Request $request)
    {
        // Verify the status type and its widget exist
        $statusType = StatusType::findOrFail($id);

        if ($request->ajax()) {
            // Build base query: DeviceStatus records for this status_type_id
            $query = DeviceStatus::with(['device', 'user', 'device_log.cam_payloads'])
                ->select('device_status.*')
                ->where('device_status.status_type_id', $id);

            // Date range filter
            if ($request->filled('date')) {
                $dates = explode(' - ', $request->date);
                if (count($dates) === 2) {
                    $start = Carbon::parse($dates[0]);
                    $end   = Carbon::parse($dates[1]);
                    $query->whereBetween('device_status.created_at', [$start, $end]);
                }
            }

            // Location filters — preserving the original multi-value branch/building/room logic
            $query->whereHas('device', function ($subQuery) use ($request) {
                if (!empty($request->branches)) {
                    $subQuery->where(function ($w) use ($request) {
                        foreach ($request->branches as $branch) {
                            $w->orWhere('branch', $branch);
                        }
                    });
                }

                if (!empty($request->buildings)) {
                    $subQuery->where(function ($w) use ($request) {
                        foreach ($request->buildings as $building) {
                            $w->orWhere('building', $building);
                        }
                    });
                }

                if (!empty($request->rooms)) {
                    $subQuery->where(function ($w) use ($request) {
                        foreach ($request->rooms as $room) {
                            $w->orWhere('room', $room);
                        }
                    });
                }
            });

            // Global search — covers device_id, branch, building, notes, and user name
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('device_status.notes', 'ILIKE', "%{$search}%")
                      ->orWhereHas('device', function ($subQuery) use ($search) {
                          $subQuery->where('device_id', 'ILIKE', "%{$search}%")
                                   ->orWhere('branch', 'ILIKE', "%{$search}%")
                                   ->orWhere('building', 'ILIKE', "%{$search}%")
                                   ->orWhere('room', 'ILIKE', "%{$search}%");
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
                ->make(true);
        }

        // Non-AJAX: serve the page shell with filter dropdown data
        $branches  = Device::select('branch')->whereNotNull('branch')->distinct()->orderBy('branch')->get();
        $buildings = Device::select('branch', 'building')->whereNotNull('building')->distinct()->orderBy('branch')->orderBy('building')->get();
        $rooms     = Device::select('building', 'room')->whereNotNull('room')->distinct()->orderBy('building')->orderBy('room')->get();

        return view('admin.status_types.history', compact('id', 'statusType', 'branches', 'buildings', 'rooms'));
    }

    /**
     * Export all filtered history records for a given status type as a streamed CSV.
     * Mirrors the pattern of DeviceStatusController::export() and DeviceLogController::export().
     *
     * @param  int  $id  The status_type_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export($id, Request $request)
    {
        StatusType::findOrFail($id); // 404 if status type does not exist

        $query = DeviceStatus::with(['device', 'user', 'device_log.cam_payloads'])
            ->select('device_status.*')
            ->where('device_status.status_type_id', $id);

        // Date range filter
        if ($request->filled('date')) {
            $dates = explode(' - ', $request->date);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0]);
                $end   = Carbon::parse($dates[1]);
                $query->whereBetween('device_status.created_at', [$start, $end]);
            }
        }

        // Location filters (multi-value arrays, preserving original logic)
        $query->whereHas('device', function ($subQuery) use ($request) {
            if (!empty($request->branches)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->branches as $branch) {
                        $w->orWhere('branch', $branch);
                    }
                });
            }
            if (!empty($request->buildings)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->buildings as $building) {
                        $w->orWhere('building', $building);
                    }
                });
            }
            if (!empty($request->rooms)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->rooms as $room) {
                        $w->orWhere('room', $room);
                    }
                });
            }
        });

        // Global search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('device_status.notes', 'ILIKE', "%{$search}%")
                  ->orWhereHas('device', function ($subQuery) use ($search) {
                      $subQuery->where('device_id', 'ILIKE', "%{$search}%")
                               ->orWhere('branch', 'ILIKE', "%{$search}%")
                               ->orWhere('building', 'ILIKE', "%{$search}%")
                               ->orWhere('room', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortCol = $request->get('sort', 'device_status.created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortCol, $sortDir);

        $filename = 'status_type_history_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Header row — matches visible table columns exactly
            fputcsv($handle, [
                'Time',
                'Device ID',
                'Locations',
                'Sub Location',
                'Location-id',
                'Notes',
                'Updated By',
                'Last Updated',
                'Cams',
                'LatLong',
            ]);

            $query->chunk(2000, function ($statuses) use ($handle) {
                foreach ($statuses as $status) {
                    // Resolve cam file URLs and latlong from the related cam_payloads
                    $camUrls  = [];
                    $latLongs = [];
                    if ($status->device_log && $status->device_log->cam_payloads) {
                        foreach ($status->device_log->cam_payloads as $cam) {
                            $camUrls[]  = rtrim(config('app.url'), '/') . Storage::url($cam->file);
                            $latLongs[] = $cam->latlong ?? '';
                        }
                    }

                    fputcsv($handle, [
                        $status->created_at?->format('Y-m-d H:i:s'),
                        $status->device?->device_id,
                        $status->device?->branch,
                        $status->device?->building,
                        $status->device?->room,
                        $status->notes,
                        $status->user?->name,
                        $status->updated_at?->format('Y-m-d H:i:s'),
                        implode(', ', $camUrls),
                        implode(', ', $latLongs),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all filtered history records for a given status type as an Excel (SpreadsheetML .xls) file.
     * Uses Excel 2003 XML format — no additional packages required.
     */
    public function exportExcel($id, Request $request)
    {
        StatusType::findOrFail($id);

        $query = $this->buildHistoryExportQuery($id, $request);

        $filename = 'status_type_history_' . now()->format('Ymd_His') . '.xls';
        $headers  = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columnHeaders = ['Time', 'Device ID', 'Locations', 'Sub Location', 'Location-id', 'Notes', 'Updated By', 'Last Updated', 'Cams', 'LatLong'];

        $callback = function () use ($query, $columnHeaders) {
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            echo '  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
            echo '<Styles><Style ss:ID="header"><Font ss:Bold="1"/></Style></Styles>' . "\n";
            echo '<Worksheet ss:Name="History"><Table>' . "\n";

            // Header row
            echo '<Row>';
            foreach ($columnHeaders as $h) {
                echo '<Cell ss:StyleID="header"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1, 'UTF-8') . '</Data></Cell>';
            }
            echo '</Row>' . "\n";

            // Data rows
            $query->chunk(2000, function ($statuses) {
                foreach ($statuses as $status) {
                    [$camUrls, $latLongs] = $this->resolveCamData($status);

                    $cells = [
                        $status->created_at?->format('Y-m-d H:i:s') ?? '',
                        $status->device?->device_id ?? '',
                        $status->device?->branch ?? '',
                        $status->device?->building ?? '',
                        $status->device?->room ?? '',
                        $status->notes ?? '',
                        $status->user?->name ?? '',
                        $status->updated_at?->format('Y-m-d H:i:s') ?? '',
                        implode(', ', $camUrls),
                        implode(', ', $latLongs),
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
     * Build the shared filtered query used by export() and exportExcel().
     */
    private function buildHistoryExportQuery($id, Request $request)
    {
        $query = DeviceStatus::with(['device', 'user', 'device_log.cam_payloads'])
            ->select('device_status.*')
            ->where('device_status.status_type_id', $id);

        if ($request->filled('date')) {
            $dates = explode(' - ', $request->date);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0]);
                $end   = Carbon::parse($dates[1]);
                $query->whereBetween('device_status.created_at', [$start, $end]);
            }
        }

        $query->whereHas('device', function ($subQuery) use ($request) {
            if (!empty($request->branches)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->branches as $branch) { $w->orWhere('branch', $branch); }
                });
            }
            if (!empty($request->buildings)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->buildings as $building) { $w->orWhere('building', $building); }
                });
            }
            if (!empty($request->rooms)) {
                $subQuery->where(function ($w) use ($request) {
                    foreach ($request->rooms as $room) { $w->orWhere('room', $room); }
                });
            }
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('device_status.notes', 'ILIKE', "%{$search}%")
                  ->orWhereHas('device', function ($subQuery) use ($search) {
                      $subQuery->where('device_id', 'ILIKE', "%{$search}%")
                               ->orWhere('branch', 'ILIKE', "%{$search}%")
                               ->orWhere('building', 'ILIKE', "%{$search}%")
                               ->orWhere('room', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $query->orderBy(
            $request->get('sort', 'device_status.created_at'),
            $request->get('dir', 'desc')
        );

        return $query;
    }

    /**
     * Resolve cam file URLs and latlong values from a DeviceStatus record.
     * Returns [$camUrls[], $latLongs[]] — uses APP_URL for absolute URLs.
     */
    private function resolveCamData($status): array
    {
        $camUrls  = [];
        $latLongs = [];

        if ($status->device_log && $status->device_log->cam_payloads) {
            foreach ($status->device_log->cam_payloads as $cam) {
                $camUrls[]  = rtrim(config('app.url'), '/') . Storage::url($cam->file);
                $latLongs[] = $cam->latlong ?? '';
            }
        }

        return [$camUrls, $latLongs];
    }
}
