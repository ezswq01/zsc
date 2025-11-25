<?php

namespace App\Http\Controllers;

use App\Events\CamDataEvent;
use App\Models\Device;
use App\Models\DeviceLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DeviceLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:device-logs-read')->only('index');
    }

    /**
     * Display a listing of the resource and handle AJAX requests for DataTables.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DeviceLog::with('device')->select('device_logs.*');

            // Date range filter
            if ($request->filled('date')) {
                $dates = explode(' - ', $request->date);
                if (count($dates) == 2) {
                    $start = Carbon::parse($dates[0])->startOfDay();
                    $end   = Carbon::parse($dates[1])->endOfDay();
                    $query->whereBetween('device_logs.created_at', [$start, $end]);
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
                    $q->where('device_logs.value', 'ILIKE', "%{$search}%")
                      ->orWhere('device_logs.type', 'ILIKE', "%{$search}%")
                      ->orWhereHas('device', function ($q2) use ($search) {
                          $q2->where('device_id', 'ILIKE', "%{$search}%")
                             ->orWhere('branch', 'ILIKE', "%{$search}%")
                             ->orWhere('building', 'ILIKE', "%{$search}%");
                      });
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('device_id', function ($model) {
                    return $model->device ? $model->device->device_id : '-';
                })
                ->addColumn('branch', function ($model) {
                    return $model->device ? $model->device->branch : '-';
                })
                ->addColumn('building', function ($model) {
                    return $model->device ? $model->device->building : '-';
                })
                ->editColumn('type', function ($model) {
                    if ($model->type === 'publish') return 'host';
                    if ($model->type === 'subscribe') return 'client';
                    return $model->type;
                })
                ->editColumn('created_at', function ($model) {
                    return $model->created_at ? $model->created_at->format('Y-m-d H:i:s') : '';
                })
                ->setRowAttr([
                    'data-model-id' => fn ($model) => $model->id,
                ])
                ->toJson();
        }

        // FIX: Fetch distinct branches and buildings for the filter dropdowns on initial page load
        $branches = Device::select('branch')->whereNotNull('branch')->distinct()->orderBy('branch')->get();
        $buildings = Device::select('branch', 'building')->whereNotNull('building')->distinct()->orderBy('branch')->orderBy('building')->get();

        return view('admin.device_logs.index', compact('branches', 'buildings'));
    }

    /**
     * Export device logs to a CSV file respecting filters.
     */
    public function export(Request $request)
    {
        $query = DeviceLog::with('device')->select('device_logs.*');

        // Apply Date filter
        if ($request->filled('date')) {
            $dates = explode(' - ', $request->date);
            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0])->startOfDay();
                $end   = Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('device_logs.created_at', [$start, $end]);
            }
        }

        // Apply Location filters
        $query->whereHas('device', function ($subQuery) use ($request) {
            if ($request->filled('branch')) {
                $subQuery->where('branch', $request->branch);
            }
            if ($request->filled('building')) {
                $subQuery->where('building', $request->building);
            }
        });

        // Apply Global search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('value', 'ILIKE', "%{$search}%")
                  ->orWhere('type', 'ILIKE', "%{$search}%")
                  ->orWhereHas('device', fn($sub) =>
                      $sub->where('device_id', 'ILIKE', "%{$search}%")
                         ->orWhere('branch', 'ILIKE', "%{$search}%")
                         ->orWhere('building', 'ILIKE', "%{$search}%")
                  );
            });
        }

        // Apply Sorting
        $sortCol = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortCol, $sortDir);

        $filename = 'device_logs_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Device ID', 'Location', 'Sub Location', 'Value', 'Type', 'Created At']);

            $query->chunk(2000, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    $type = $log->type;
                    if ($type === 'publish') $type = 'host';
                    if ($type === 'subscribe') $type = 'client';

                    fputcsv($handle, [
                        $log->device?->device_id,
                        $log->device?->branch,
                        $log->device?->building,
                        $log->value,
                        $type,
                        $log->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

	public function camPayload(Request $request) 
	{
		$request->validate([
			'file' => 'required|file',
			'latlong' => 'required'
		]);

		try {
			$file = $request->file('file');
			$file_name = $file->getClientOriginalName();
			$app = explode('_', $file_name);
			$payload_id = explode('.', $app[1])[0];
			$file_ext = $file->getClientOriginalExtension();

			if ($app[0] !== 'cambymcc') {
					return response()->json([
						'success' => false,
						'message' => 'Invalid app code. Please use the correct app code',
					], 400);
			}

			$device = Device::where('id', DeviceLog::find($payload_id)->device_id)->first();
			if (!$device) {
					return response()->json([
						'success' => false,
						'message' => 'Device not found.',
					], 404);
			}
			if (!isset($device->cam_topic)) {
					$cam_topic = implode('/', array(
						Setting::first()->mqtt_main_topic ?? "mcc",
						str_replace(" ","-", strtolower($device->branch)),
						str_replace(" ","-", strtolower($device->building)),
						str_replace(" ","-", strtolower($device->room)),
						str_replace(" ","-", strtolower($device->device_id)),
						"cambymcc"
					));
					$device->cam_topic = $cam_topic;
					$device->save();
			}
			$cam_topic = $device->cam_topic;
			$cam_topic = str_replace('/', '_', $cam_topic);
			$file_name_original = $cam_topic . '_' . now()->format('YmdHis') . '.' . $file_ext;
			$path = $file->storeAs('payloads/cam', $file_name_original, 'public');

			DB::table('cam_payloads')->insert([
					'device_log_id' => $payload_id,
					'file_name' => $file_name,
					'file' => $path,
					'created_at' => now(),
					'updated_at' => now(),
					'latlong' => $request->latlong,
			]);

			CamDataEvent::dispatch([
					'type' => 'dynamic_device',
					'data' => DeviceLog::find($payload_id)->load('cam_payloads'),
			]);
			
		} catch (\Exception $e) {
			if (isset($path) && Storage::disk('public')->exists($path)) {
					Storage::disk('public')->delete($path);
			}
			return response()->json([
					'success' => false,
					'message' => $e->getMessage(),
			], 500);
		}

		return response()->json([
			'success' => true,
			'message' => 'Cam payload saved successfully.',
		], 200);
	}
}
