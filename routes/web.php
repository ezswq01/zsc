<?php

use App\Http\Controllers\AbsentDeviceController;
use App\Http\Controllers\AbsentDeviceLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceLogController;
use App\Http\Controllers\DeviceStatusController;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StatusTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard.index');
});

Route::get('/login', [AuthController::class, 'loginGet'])->name('login');
Route::post('/login', [AuthController::class, 'loginPost'])->name('login.post');

Route::name('admin.')->prefix('admin')->middleware('auth')->group(function () {
    Route::resource('settings', SettingController::class)->only('index', 'update');
    Route::resource('devices', DeviceController::class);
    Route::resource('device_types', DeviceTypeController::class);
    Route::resource('status_types', StatusTypeController::class);
    Route::resource('device_logs', DeviceLogController::class)->only(['index']);
    Route::resource('absent_device_logs', AbsentDeviceLogController::class)->only(['index']);
    Route::resource('dashboard', DashboardController::class)->only(['index']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('absent_devices', AbsentDeviceController::class);
    Route::resource('device_statuses', DeviceStatusController::class)->only(['index']);

    // Solo page
    Route::get('/status_types/{id}/history', [StatusTypeController::class, 'history'])->name('status_types.history');

    // Logout
    Route::get('/change-password', [AuthController::class, 'changePassword']);
    Route::put('/change-password', [AuthController::class, 'changePasswordStore']);
    Route::get('/logout', [AuthController::class, 'logoutPost'])->name('logout');

    // Ajax and Export routes
    Route::get('/device_statuses/ajax', [DeviceStatusController::class, 'ajax'])->name('device_statuses.ajax');
    Route::get('/device-statuses/export', [DeviceStatusController::class, 'export'])->name('device_statuses.export');
    Route::get('/device-logs/export', [DeviceLogController::class, 'export'])->name('device_logs.export');
    
    Route::post('/devices/publish', [DeviceController::class, 'publish'])->name('devices.publish');
    Route::post('/devices/publish-streaming', [DeviceController::class, 'publishStreaming'])->name('devices.publish-streaming');
    Route::post('/devices/publish-streaming-stop', [DeviceController::class, 'publishStreamingStop'])->name('devices.publish-streaming-stop');
    Route::post('/devices/get-registered-locations', [DeviceController::class, 'getRegisteredLocations'])->name('devices.get-reg-locations');
    Route::post('/devices/get-hour', [DeviceController::class, 'getHours'])->name('devices.get-hour');
    Route::post('/devices/set-active-hour', [DeviceController::class, 'setActiveHours'])->name('devices.set-active-hour');
    Route::post('/devices/set-inactive-hour', [DeviceController::class, 'setInactiveHours'])->name('devices.set-inactive-hour');
    Route::post('/absent_devices/publish', [AbsentDeviceController::class, 'publish'])->name('absent_devices.publish');
    Route::get('/api/devices/device_branches', [DeviceController::class, 'branches'])->name('devices.branches');
    Route::get('/api/devices/device_buildings', [DeviceController::class, 'buildings'])->name('devices.buildings');
    Route::get('/api/devices/device_types', [DeviceController::class, 'device_types'])->name('devices.device_types');
    Route::get('/device_rooms', [DeviceController::class, 'rooms'])->name('devices.rooms');

    Route::post('/device_status/notes', [DeviceStatusController::class, 'notes'])->name('device_status.notes');
    Route::get('/device_status/{id}', [DeviceStatusController::class, 'get_device_status'])->name('device_status.get_device_status');

    Route::resource('/notifications', NotifController::class)->only(['index']);
    Route::get('/notifications/{id}/read', [NotifController::class, 'read'])->name('notifications.read');
});
