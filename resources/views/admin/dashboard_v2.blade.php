@extends("admin.layout.main")

@push("style")
<style>
  .select2-search__field {
    width: 100% !important
  }
  [x-cloak] { 
    display: none !important; 
  }
  /* Ensure the video stream modal appears on top of all other modals */
  #video-stream-modal {
    z-index: 1060; 
  }
  /* Ensure dropdowns within modals are visible */
  .modal .dropdown-menu {
    z-index: 1061;
  }
</style>
@endpush

@push("header")
<div class="page-header page-header-light">
  <div class="page-header-content d-lg-flex">
    <div class="d-flex">
      <h4 class="page-title mb-0">
        Dashboard
      </h4>
    </div>
  </div>
  <div class="page-header-content d-lg-flex">
    <div class="d-flex flex-fill w-xl-75 w-100">
      <div class="breadcrumb py-2">
        <a class="breadcrumb-item" href="/admin/dashboard"><i class="ph-house"></i></a>
        <a class="breadcrumb-item" href="#">Dashboard</a>
      </div>
      <a class="btn btn-light align-self-center collapsed d-lg-none border-transparent rounded-pill p-0 ms-auto"
        data-bs-toggle="collapse" href="#breadcrumb_elements">
        <i class="ph-caret-down collapsible-indicator ph-sm m-1"></i>
      </a>
    </div>
    <div class="d-flex w-100 py-2 bg-white gap-2">
      <select
        class="form-control select"
        data-placeholder="All Locations"
        name="branches"
        id="branches"
        multiple="multiple">
        <option></option>
        @foreach ($device_locations as $device_location)
        <option value="{{ $device_location->branch }}">
          {{ ucfirst($device_location->branch) }}
        </option>
        @endforeach
      </select>
      <select
        class="form-control select"
        data-placeholder="All Sub-Locations"
        name="buildings"
        id="buildings"
        multiple="multiple">
        <option></option>
        @foreach ($device_sub_locations as $device_sub_location)
        <option value="{{ $device_sub_location->building }}">
          {{ ucfirst($device_sub_location->building) }}
        </option>
        @endforeach
      </select>
      <select
        class="form-control select"
        data-placeholder="All Location-ID"
        name="rooms"
        id="rooms"
        multiple="multiple">
        <option></option>
        @foreach ($device_location_ids as $device_location_id)
        <option value="{{ $device_location_id->room }}">
          {{ ucfirst($device_location_id->room) }}
        </option>
        @endforeach
      </select>
    </div>
  </div>
</div>
@endpush

@section("content")
@php
$setting = App\Models\Setting::first();
@endphp
<div x-data="dashboard" x-init="init()" x-cloak>
    {{-- Main Modals --}}
    <div class="modal fade" id="card-widget-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title" x-text="data?.status_type_widgets?.find(item => item.status_type.id == selectedStatusType)?.status_type?.name?.toUpperCase()"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body overflow-auto text-nowrap">
                    <table class="table table-center">
                        <thead><tr><th>Actions</th><th>Status</th><th>Time</th><th>Identity</th><th>Location</th><th>Cams</th><th>LatLong</th></tr></thead>
                        <tbody>
                            <template x-for="item in data?.status_type_widgets?.find(item => item.status_type.id == selectedStatusType)?.status_type?.device_status">
                                <tr>
                                    <td class="align-middle"><button data-bs-target="#card-widget-note-modal" data-bs-toggle="modal" @click="selectedDeviceStatus = item" class="btn btn-success"><i class="ph-eye"></i></button></td>
                                    <td class="align-middle">
                                        <template x-if="item.marked_as_read"><button class="btn btn-success"><i class="ph-check-circle"></i></button></template>
                                        <template x-if="!item.marked_as_read"><button class="btn btn-danger"><i class="ph-question"></i></button></template>
                                    </td>
                                    <td class="align-middle"><span x-text="moment(item.created_at).format('YYYY-MM-DD HH:mm:ss')"></span></td>
                                    <td class="align-middle"><ul class="mb-0"><li>LOG ID: <span x-text="item.device_log.id"></span></li><li>DEVICE ID: <span x-text="item.device_id"></span></li></ul></td>
                                    <td class="align-middle"><ul class="mb-0"><li><span x-text="item.device?.branch?.toUpperCase()"></span></li><li><span x-text="item.device?.building?.toUpperCase()"></span></li><li><span x-text="item.device?.room?.toUpperCase()"></span></li></ul></td>
                                    <td class="align-middle"><ul class="mb-0"><template x-for="cam in item.device_log?.cam_payloads"><li><a target="_blank" :href="`/storage/${cam.file}`"><span x-text="cam.file_name"></span>-id: <span x-text="cam.id"></span></a></li></template><template x-if="!item.device_log?.cam_payloads?.length"><li>No Image Available</li></template></ul></td>
                                    <td class="align-middle"><ul class="mb-0"><template x-for="cam in item.device_log?.cam_payloads"><li><a target="_blank" :href="`https://www.google.com/maps/search/?api=1&query=${cam.latlong}`"><span x-text="cam.latlong"></span>-id: <span x-text="cam.id"></span></a></li></template><template x-if="!item.device_log?.cam_payloads?.length"><li>No Coordinate Available</li></template></ul></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="card-widget-note-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title"><span x-text="'NOTE FOR LOG ID: ' + selectedDeviceStatus?.device_log?.id + ' - DEVICE ID: ' + selectedDeviceStatus?.device_id"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <template x-if="selectedDeviceStatus"><textarea class="form-control mb-2" rows="5" style="resize: none;" x-model="selectedDeviceStatus.notes"></textarea></template>
                    <div class="d-flex gap-2 align-items-center"><span>State: </span><span class="state" :class="selectedDeviceStatus?.is_normal_state ? 'btn btn-success' : 'btn btn-danger'"><span x-text="selectedDeviceStatus?.is_normal_state ? 'Normal' : 'Not Normal'"></span></span></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" @click="stream(selectedDeviceStatus?.device_id)"><i class="ph-video-camera me-2"></i>STREAM</button>
                    <template x-for="action in selectedDeviceStatus?.device?.publish_action"><button type="button" class="btn btn-success" @click="publishAction(action.id)"><span x-text="action.label?.toUpperCase()"></span></button></template>
                    <button type="button" class="btn btn-link ms-auto" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" @click="submitNote">Submit Note</button>
                </div>
            </div>
        </div>
    </div>

    {{-- New Video Streaming Modal --}}
    <div class="modal fade" id="video-stream-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Video Streaming</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div style="aspect-ratio: 16/9" :style="{ display: isStreamingLoading ? 'flex' : 'none' }" class="w-100 flex-column gap-2 justify-content-center align-items-center"><div class="spinner-border spinner-border-lg" role="status"><span class="visually-hidden">Loading...</span></div><span>Please wait...</span></div>
                    <iframe style="aspect-ratio: 16/9; border: 0;" :style="{ display: isStreaming ? 'block' : 'none', backgroundColor: 'black' }" class="w-100" :src="iFrameUrl"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- Location Modals --}}
    @if($setting->location_widget)
    <div class="modal fade" tabindex="-1" id="registeredLocationModal"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">REGISTERED LOCATION</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body overflow-auto text-nowrap"><input type="text" x-model="locationSearch.registered" class="form-control mb-3" placeholder="Search by Location ID..."><table class="table table-center"><thead><tr><th>Location ID</th><th>Last Ping</th><th>Active Hour</th><th>Inactive Hour</th><th>Location Status</th></tr></thead><tbody><template x-for="(value, key) in filteredRegisteredLocations" :key="key"><tr><td class="align-middle"><span x-text="key"></span></td><td class="align-middle"><span x-text="value[0]['last_ping_at'] || 'No ping data'"></span></td><td class="align-middle"><span x-text="value[0]['active_hour'] || 'No active hour'"></span></td><td class="align-middle"><span x-text="value[0]['inactive_hour'] || 'No inactive hour'"></span></td><td class="align-middle"><span class="badge" :class="Object.keys(activeLocations).includes(key) ? 'bg-success' : 'bg-danger'" x-text="Object.keys(activeLocations).includes(key) ? 'Active' : 'Inactive'"></span></td></tr></template></tbody></table></div><div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button></div></div></div></div>
    <div class="modal fade" tabindex="-1" id="activeLocationModal"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">ACTIVE LOCATION</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body overflow-auto text-nowrap"><input type="text" x-model="locationSearch.active" class="form-control mb-3" placeholder="Search by Location ID..."><table class="table table-center"><thead><tr><th>Location ID</th><th>Last Ping</th><th>Active Hour</th><th>Inactive Hour</th><th>Action</th></tr></thead><tbody><template x-for="(value, key, index) in filteredActiveLocations" :key="key"><tr><td class="align-middle"><span x-text="key"></span></td><td class="align-middle"><span x-text="value[0]['last_ping_at'] || 'No ping data'"></span></td><td class="align-middle"><span x-text="value[0]['active_hour'] || 'No active hour'"></span></td><td class="align-middle"><span x-text="value[0]['inactive_hour'] || 'No inactive hour'"></span></td><td class="align-middle text-center"><div class="dropdown"><button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button><ul class="dropdown-menu"><li><a class="dropdown-item" href="#" @click.prevent="stream(value[0]['id'])">Stream</a></li><li><a class="dropdown-item" href="#" @click.prevent="getHour(value[0]['id'])">Get Active Period</a></li><li><a class="dropdown-item" href="#" @click.prevent="setActiveHour(value[0]['id'])">Set Active Hours</a></li><li><a class="dropdown-item" href="#" @click.prevent="setInctiveHour(value[0]['id'])">Set Inactive Hours</a></li></ul></div></td></tr></template></tbody></table></div><div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button></div></div></div></div>
    <div class="modal fade" tabindex="-1" id="inactiveLocationModal"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">INACTIVE LOCATION</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body overflow-auto text-nowrap"><input type="text" x-model="locationSearch.inactive" class="form-control mb-3" placeholder="Search by Location ID..."><table class="table table-center"><thead><tr><th>Location ID</th><th>Last Ping</th><th>Active Hour</th><th>Inactive Hour</th><th>Last Camera Captured</th></tr></thead><tbody><template x-for="(value, key, index) in filteredInactiveLocations" :key="key"><tr><td class="align-middle"><span x-text="key"></span></td><td class="align-middle"><span x-text="value[0]['last_ping_at'] || 'No ping data'"></span></td><td class="align-middle"><span x-text="value[0]['active_hour'] || 'No active hour'"></span></td><td class="align-middle"><span x-text="value[0]['inactive_hour'] || 'No inactive hour'"></span></td><td class="align-middle"><template x-if="getLastCaptureForDevice(value[0].device_id)"><a :href="getLastCaptureForDevice(value[0].device_id)" target="_blank">View Capture</a></template><template x-if="!getLastCaptureForDevice(value[0].device_id)"><span>N/A</span></template></td></tr></template></tbody></table></div><div class="modal-footer"><button type="button" class="btn btn-link" data-bs-dismiss="modal">Close</button></div></div></div></div>
    @endif

    {{-- Main Content Area --}}
    <div class="row gx-3">
        @if($setting->is_access_device)
        <div class="col-lg-3 col-12"><div class="card text-white shadow-lg" :style="{ backgroundColor: data.absent_received_logs.length > 0 ? 'rgb(200, 0, 0)' : 'rgb(0, 100, 0)' }"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><h3 class="mb-0 display-4" x-text="data.absent_received_logs.length"></h3><div class="d-flex justify-content-between align-items-start gap-2"></div></div><h6>ABSENT DEVICE</h6></div></div></div>
        @endif
        @if($setting->location_widget)
        <div class="col-lg-3 col-12"><div class="card text-white shadow-lg" style="background-color: rgb(0, 100, 0);"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><h3 class="mb-0 display-4"><span x-text="registeredLocations ? Object.keys(registeredLocations).length : 0"></span></h3><div class="d-flex justify-content-between align-items-start gap-2"><button type="button" class="btn btn-white p-1" data-bs-toggle="modal" data-bs-target="#registeredLocationModal"><i class="ph-table"></i></button></div></div><h6>REGISTERED LOCATION</h6></div></div></div>
        <div class="col-lg-3 col-12"><div class="card text-white shadow-lg" style="background-color: rgb(0, 100, 0);"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><h3 class="mb-0 display-4"><span x-text="activeLocations ? Object.keys(activeLocations).length : 0"></span></h3><div class="d-flex justify-content-between align-items-start gap-2"><button type="button" class="btn btn-white p-1" data-bs-toggle="modal" data-bs-target="#activeLocationModal"><i class="ph-table"></i></button></div></div><h6>ACTIVE LOCATION</h6></div></div></div>
        <div class="col-lg-3 col-12"><div class="card text-white shadow-lg" style="background-color: rgb(0, 100, 0);"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><h3 class="mb-0 display-4"><span x-text="inactiveLocations ? Object.keys(inactiveLocations).length : 0"></span></h3><div class="d-flex justify-content-between align-items-start gap-2"><button type="button" class="btn btn-white p-1" data-bs-toggle="modal" data-bs-target="#inactiveLocationModal"><i class="ph-table"></i></button></div></div><h6>INACTIVE LOCATION</h6></div></div></div>
        @endif
        <template x-for="item in data?.status_type_widgets">
            <div class="col-lg-3 col-12">
                <div class="card text-white shadow-lg" :style="{ backgroundColor: item.status_type.device_status.length === 0 ? item.status_type.color : item.status_type.trigger_color }">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h3 class="mb-0 display-4"><span x-text="item.status_type?.device_status?.length"></span></h3>
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <button type="button" class="btn btn-white p-1" @click="selectedStatusType = item.status_type.id" data-bs-target="#card-widget-modal" data-bs-toggle="modal"><i class="ph-table"></i></button>
                                <a class="btn btn-white p-1" :href="`/admin/status_types/${item.status_type.id}/history`" target="_blank"><i class="ph-clock"></i></a>
                            </div>
                        </div>
                        <h6 x-text="item.status_type?.name?.toUpperCase()"></h6>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
@endsection

@push('js')
<script src="//unpkg.com/alpinejs" defer></script>
<script>
  document.addEventListener('alpine:init', () => {
    var audio = new Audio('/mcc-notification.wav');
    Alpine.data('dashboard', () => ({
      init() {
        this.socketListener();
        this.setupModalListeners();
        this.triggerFetch();
        this.getRegisteredLocation();
        setInterval(() => this.getRegisteredLocation(), 1000 * 60 * 1);
        
        $('#branches, #buildings, #rooms').select2({ width: '100%' });
        $('#branches').on('change', () => { this.branches = $('#branches').val(); this.triggerFetch(); });
        $('#buildings').on('change', () => { this.buildings = $('#buildings').val(); this.triggerFetch(); });
        $('#rooms').on('change', () => { this.rooms = $('#rooms').val(); this.triggerFetch(); });
      },

      // --- STATE ---
      data: { absent_received_logs: [], status_type_widgets: [] },
      selectedStatusType: null,
      selectedDeviceStatus: null,
      isModalOpen: false,
      pendingUpdate: false,
      branches: [], buildings: [], rooms: [],
      isStreaming: false, isStreamingLoading: false, iFrameUrl: "",
      streamingDeviceId: null,
      registeredLocations: {}, activeLocations: {}, inactiveLocations: {},
      locationSearch: { registered: '', active: '', inactive: '' },

      // --- COMPUTED PROPERTIES FOR SEARCH ---
      get filteredRegisteredLocations() {
        if (!this.locationSearch.registered) return this.registeredLocations;
        return Object.fromEntries(Object.entries(this.registeredLocations).filter(([key]) => key.toLowerCase().includes(this.locationSearch.registered.toLowerCase())));
      },
      get filteredActiveLocations() {
        if (!this.locationSearch.active) return this.activeLocations;
        return Object.fromEntries(Object.entries(this.activeLocations).filter(([key]) => key.toLowerCase().includes(this.locationSearch.active.toLowerCase())));
      },
      get filteredInactiveLocations() {
        if (!this.locationSearch.inactive) return this.inactiveLocations;
        return Object.fromEntries(Object.entries(this.inactiveLocations).filter(([key]) => key.toLowerCase().includes(this.locationSearch.inactive.toLowerCase())));
      },

      // --- METHODS ---
      async triggerFetch() {
        let params = new URLSearchParams();
        (this.branches || []).forEach(b => params.append('branches[]', b));
        (this.buildings || []).forEach(b => params.append('buildings[]', b));
        (this.rooms || []).forEach(r => params.append('rooms[]', r));
        
        let url = '{{ route("dashboard.ajax") }}?' + params.toString();
        await this.getFetch(url);
      },
      async getFetch(url) {
        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const res = await response.json();
        this.data = this.status_type_widgets_convert(res);
      },
      status_type_widgets_convert(res) {
          return {
              absent_received_logs: res.absent_received_logs.filter(adl => !adl.marked_as_read),
              status_type_widgets: res.status_type_widgets.map(stw => ({
                  ...stw,
                  status_type: {
                      ...stw.status_type,
                      device_status: stw.status_type.device_status.filter(item => !item.marked_as_read && item.notes !== "Normal State")
                  }
              }))
          };
      },
      getLastCaptureForDevice(deviceId) {
          for (const widget of this.data.status_type_widgets) {
              for (const status of widget.status_type.device_status) {
                  if (status.device_id === deviceId && status.device_log?.cam_payloads?.length > 0) {
                      // Assuming the last payload is the most recent
                      const lastPayload = status.device_log.cam_payloads[status.device_log.cam_payloads.length - 1];
                      return `/storage/${lastPayload.file}`;
                  }
              }
          }
          return null;
      },
      async submitNote() {
        if (!this.selectedDeviceStatus) return;
        $.ajax({
          url: '/admin/device_status/notes', type: 'POST',
          data: { _token: '{{ csrf_token() }}', device_status_id: this.selectedDeviceStatus.id, notes: this.selectedDeviceStatus.notes },
          success: (res) => { 
              alert('Note submitted!'); 
              bootstrap.Modal.getInstance($('#card-widget-note-modal')[0]).hide();
              this.triggerFetch();
          },
          error: (err) => { 
              console.error("Submit Note Error:", err.responseJSON || err.responseText);
              alert('Error submitting note. Check console for details.');
          }
        });
      },
      async publishAction(actionId) {
        if (!this.selectedDeviceStatus) return;
        $.ajax({
          url: '/admin/devices/publish', type: 'POST',
          data: { _token: '{{ csrf_token() }}', id: actionId, device_status_id: this.selectedDeviceStatus.id, log_id: this.selectedDeviceStatus.device_log.id, notes: this.selectedDeviceStatus.notes },
          success: (res) => { 
              alert('Action published!'); 
              bootstrap.Modal.getInstance($('#card-widget-note-modal')[0]).hide();
              this.triggerFetch();
          },
          error: (err) => { 
              console.error("Publish Action Error:", err.responseJSON || err.responseText);
              alert(`Server Error: ${err.status} ${err.statusText}. Check console for details.`);
          }
        });
      },
      async stream(deviceId) {
        this.streamingDeviceId = deviceId;
        this.isStreamingLoading = true; 
        this.isStreaming = false; 
        this.iFrameUrl = "";
        
        let streamModal = bootstrap.Modal.getInstance(document.getElementById('video-stream-modal'));
        if (!streamModal) {
            streamModal = new bootstrap.Modal(document.getElementById('video-stream-modal'));
        }
        streamModal.show();

        $.ajax({
          url: '/admin/devices/publish-streaming', type: 'POST', data: { _token: '{{ csrf_token() }}', device_id: deviceId },
          error: (err) => { 
              this.isStreamingLoading = false; 
              console.error("Stream Error:", err.responseJSON || err.responseText);
              alert('Failed to request stream. Check console for details.');
          }
        });
      },
      async getRegisteredLocation() {
        $.ajax({
          url: '/admin/devices/get-registered-locations', type: 'post', data: { _token: '{{ csrf_token() }}' },
          success: (res) => {
            this.registeredLocations = res.data.registeredLocations || {};
            this.activeLocations = res.data.activeLocations || {};
            this.inactiveLocations = res.data.inactiveLocations || {};
          }
        });
      },
      async getHour(deviceId) { $.ajax({ url: '/admin/devices/get-hour', type: 'post', data: { _token: '{{ csrf_token() }}', device_id: deviceId }, success: () => alert('Request sent!'), error: () => alert('Error!') }); },
      async setActiveHour(deviceId) { const time = prompt('Time (HH:mm):'); if(time) $.ajax({ url: '/admin/devices/set-active-hour', type: 'post', data: { _token: '{{ csrf_token() }}', device_id: deviceId, time: time }, success: () => alert('Request sent!'), error: () => alert('Error!') }); },
      async setInctiveHour(deviceId) { const time = prompt('Time (HH:mm):'); if(time) $.ajax({ url: '/admin/devices/set-inactive-hour', type: 'post', data: { _token: '{{ csrf_token() }}', device_id: deviceId, time: time }, success: () => alert('Request sent!'), error: () => alert('Error!') }); },

      setupModalListeners() {
        document.querySelectorAll('.modal').forEach(modalEl => {
            modalEl.addEventListener('show.bs.modal', () => { this.isModalOpen = true; });
            modalEl.addEventListener('hidden.bs.modal', () => {
                this.isModalOpen = false;
                if (this.pendingUpdate) {
                    this.triggerFetch();
                    this.pendingUpdate = false;
                }
                
                if (modalEl.id === 'video-stream-modal' && this.streamingDeviceId) {
                    $.ajax({ 
                        url: '/admin/devices/publish-streaming-stop', type: 'POST', data: { _token: '{{ csrf_token() }}', device_id: this.streamingDeviceId },
                        error: (err) => {
                            console.error("Stop Streaming Error:", err.responseJSON || err.responseText);
                        }
                    });
                }

                this.isStreaming = false; 
                this.isStreamingLoading = false; 
                this.iFrameUrl = "";
                this.streamingDeviceId = null;
            });
        });
      },

      socketListener() {
        window.Echo.channel('laravel_database_newDataChannel')
          .listen('.newDataEvent', (e) => {
            if (e?.message?.type === "stream_listener") {
              this.iFrameUrl = 'https://' + e?.message?.plain_payload;
              this.isStreaming = true;
              this.isStreamingLoading = false;
            } else {
              if (this.isModalOpen) {
                this.pendingUpdate = true;
              } else {
                this.triggerFetch();
              }
              if (e?.message?.data?.length > 0 && e?.message?.data?.at(0)?.notes !== "Normal State") {
                audio.play();
              }
            }
          })
          .listen('.camDataEvent', (e) => {
            if (this.isModalOpen) { this.pendingUpdate = true; } else { this.triggerFetch(); }
          });
      },
    }))
  })
</script>
@endpush
