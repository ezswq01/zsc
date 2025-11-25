<?php

namespace App\Http\Controllers;

use App\Models\AbsentReceivedLog;
use App\Models\Device;
use App\Models\Setting;
use App\Models\StatusTypeWidget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $device_locations = Device::distinct()->get(['branch']);
        $device_sub_locations = Device::distinct()->get(['building']);
        $device_location_ids = Device::distinct()->get(['room']);

        return view('admin.dashboard_v2', compact(
            'device_locations',
            'device_sub_locations',
            'device_location_ids',
        ));
    }

    public function ajaxDashboard(Request $request)
    {
        Log::info("Time Query Start: " . date('Y-m-d H:i:s'));

        $status_type_widgets = StatusTypeWidget::with([
            'status_type.device_status' => function ($query) use ($request)
            {
                if ($request->date)
                {
                    $from_date = explode(' - ', request()->date)[0];
                    $to_date = explode(' - ', request()->date)[1];
                    $query->whereBetween('created_at', [$from_date, $to_date]);
                }
                return $query->orderBy('id', 'desc')
                    ->whereIn('id', function ($subquery)
                    {
                        $subquery->select(DB::raw('MAX(id)'))
                            ->from('device_status')
                            ->where('notes', '!=', 'Normal State')
                            ->where('marked_as_read', false)
                            ->groupBy('device_id');
                    })
                    ->with('device.publish_action')
                    ->with('device_log.cam_payloads')
                    ->whereHas('device', function ($query) use ($request)
                    {
                        if (!empty($request->branches))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $branches = $request->branches;
                                foreach ($branches as $branch)
                                {
                                    $w->orWhere('branch', $branch);
                                }
                            });
                        }
                        if (!empty($request->buildings))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $buildings = $request->buildings;
                                foreach ($buildings as $building)
                                {
                                    $w->orWhere('building', $building);
                                }
                            });
                        }
                        if (!empty($request->rooms))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $rooms = $request->rooms;
                                foreach ($rooms as $room)
                                {
                                    $w->orWhere('room', $room);
                                }
                            });
                        }
                        if (!empty($request->get('search')))
                        {
                            $query->where(function ($w) use ($request)
                            {
                                $search = $request->get('search');
                                $w->orWhere('device_id', 'ILIKE', "%$search%");
                            });
                        }
                    });
            }
        ])->orderBy('id', 'desc')->get();

        Log::info("Time Query End: " . date('Y-m-d H:i:s'));

        $absent_received_logs = [];

        if (Setting::first()->is_access_device)
        {
            $absent_received_logs = AbsentReceivedLog::query();
            if ($request->date)
            {
                $from_date = explode(' - ', request()->date)[0];
                $to_date = explode(' - ', request()->date)[1];
                $absent_received_logs->whereBetween('created_at', [$from_date, $to_date]);
            }
            $absent_received_logs = $absent_received_logs->with('absent_device', 'user')
                ->whereHas(
                    'absent_device',
                    function ($query) use ($request)
                    {
                        if (!empty($request->branches))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $branches = $request->branches;
                                foreach ($branches as $branch)
                                {
                                    $w->orWhere('branch', $branch);
                                }
                            });
                        }
                        if (!empty($request->buildings))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $buildings = $request->buildings;
                                foreach ($buildings as $building)
                                {
                                    $w->orWhere('building', $building);
                                }
                            });
                        }
                        if (!empty($request->rooms))
                        {
                            return $query->where(function ($w) use ($request)
                            {
                                $rooms = $request->rooms;
                                foreach ($rooms as $room)
                                {
                                    $w->orWhere('room', $room);
                                }
                            });
                        }
                        if (!empty($request->get('search')))
                        {
                            $query->where(function ($w) use ($request)
                            {
                                $search = $request->get('search');
                                $w->orWhere('absent_device_id', 'ILIKE', "%$search%");
                            });
                        }
                        return $query;
                    }
                )->orderBy('created_at', 'desc')->get();

            // group by device_id
            if ($absent_received_logs->count() > 0)
            {
                $absent_received_logs = $absent_received_logs->sortByDesc('id')
                    ->groupBy('absent_device_id')
                    ->map(function ($val)
                    {
                        return !$val->first()->marked_as_read ? $val->first() : null;
                    })
                    ->filter(function ($val)
                    {
                        return !is_null($val);
                    })
                    ->toArray();
            }
        }

        return response()->json([
            'status_type_widgets' => $status_type_widgets,
            'absent_received_logs' => count($absent_received_logs) > 0 ? array_values($absent_received_logs) : [],
        ], 200);
    }
}
