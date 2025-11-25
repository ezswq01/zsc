<?php

namespace App\Models;

use App\Jobs\TriggerJob;
use App\Mail\TriggerMail;
use App\Notifications\TriggerTelegramNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class Device extends Model
{
	// use HasFactory;

	protected $dates = [
		'created_at',
		'updated_at'
	];

	protected $fillable = [
		'device_id',
		'device_type_id',
		'publish_topic',
		'subscribe_topic',
		'cam_topic',
		'branch',
		'building',
		'room',
		'created_at',
		'updated_at',
		'is_online',
		'last_ping_at',
	];

	public function device_type()
	{
		return $this->belongsTo(DeviceType::class, 'device_type_id', 'id');
	}

	public function device_log()
	{
		return $this->hasMany(DeviceLog::class, 'device_id');
	}

	public function device_status()
	{
		return $this->hasMany(DeviceStatus::class, 'device_id');
	}

	public function publish_action()
	{
		return $this->hasMany(PublishAction::class, 'device_id');
	}

	public function subscribe_expression()
	{
		return $this->hasMany(SubscribeExpression::class, 'device_id');
	}

	public static function evalValue($device_id, $device_log_id, $subscribe_expression, $value, $device_id_unique)
	{
		$status_responses = [];
		foreach ($subscribe_expression as $val) {
			$expression = str_replace("{{value}}", "'$value'", $val->expression);
			if (eval("return $expression;")) {
				$device_status_before = DeviceStatus::where('device_id', $device_id)
					->where('status_type_id', $val->status_type_id)
					->orderBy('created_at', 'desc')
					->first();
				$notes = "";
				if ($val->normal_state && $device_status_before->noted) {
					$notes = $device_status_before->notes;
				} else {
					$notes = $val->normal_state ? "Normal State" : "";
				}
				$status_response = DeviceStatus::create([
					'device_id' => $device_id,
					'device_log_id' => $device_log_id,
					'status_type_id' => $val->status_type_id,
					'marked_as_read' => $val->normal_state && $device_status_before->noted === true ? true : false,
					'notes' => $notes,
				]);
				$status_response = $status_response->load(
					'status_type.status_type_widget', 
					'device.publish_action', 
					'device_log.cam_payloads'
				);
				$status_responses[] = $status_response;

				// notification
				$status_type = StatusType::find($val->status_type_id);
				$setting = Setting::first();
				$status_type_widgets = StatusTypeWidget::where('status_type_id', $status_type->id)
					->where('setting_id', $setting->id)
					->get();
				if ($status_type_widgets->count() > 0) {
					TriggerJob::dispatch($value, $status_type->name);
					Notification::route('telegram', $setting->chat_id_telegram)->notify(
						new TriggerTelegramNotification(
							"Trigger Alert!\nAlert from : $status_type->name with value $value"
						)
					);
					Notif::create([
						'notif_type' => 'dynamic_device',
						'notif_status' => 'unread',
						'device_id' => $device_id,
						'absent_device_id' => null,
						'message' => "Device {$device_id_unique} has new {$status_type->name}. State: "
							. ($val->normal_state ? "OK" : "NOT OK")
							. ". Value: $value",
					]);
				}
			}
		}
		return $status_responses;
	}
}
