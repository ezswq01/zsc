@extends("admin.layout.main")

@push("header")
    <div class="page-header page-header-light">
        <div class="page-header-content d-lg-flex">
            <div class="d-flex">
                <h4 class="page-title mb-0">
                    History Of Status Widget - <span class="fw-normal">{{ $statusType->name }}</span>
                </h4>
            </div>
        </div>

        <div class="page-header-content d-lg-flex">
            <div class="d-flex">
                <div class="breadcrumb py-2">
                    <a class="breadcrumb-item" href="/admin/dashboard"><i class="ph-house"></i></a>
                    <a class="breadcrumb-item" href="#">History Of Status Widget</a>
                    <span class="breadcrumb-item active">{{ $statusType->name }}</span>
                </div>
                <a class="btn btn-light align-self-center collapsed d-lg-none border-transparent rounded-pill p-0 ms-auto"
                    data-bs-toggle="collapse" href="#breadcrumb_elements">
                    <i class="ph-caret-down collapsible-indicator ph-sm m-1"></i>
                </a>
            </div>
        </div>
    </div>
@endpush

@section("content")
    <!-- Basic datatable -->
    <div class="card shadow-none">
        <div class="card-header">
            <h5 class="mb-0">History Of Status Widget — {{ $statusType->name }}</h5>
        </div>

        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between">
                {{-- Location Filters --}}
                <div class="d-flex flex-column flex-lg-row gap-2">
                    <div>
                        <select class="form-select" id="branch_filter">
                            <option value="">All Locations</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select class="form-select" id="building_filter">
                            <option value="">All Sub-locations</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->building }}" data-branch="{{ $building->branch }}" style="display: none;">{{ $building->building }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select class="form-select" id="room_filter">
                            <option value="">All Location-IDs</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->room }}" data-building="{{ $room->building }}" style="display: none;">{{ $room->room }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Date Range Picker --}}
                <div>
                    <input type="text" class="form-control datepicker-basic"
                        placeholder="Pick Start & End Date" name="date" autocomplete="off">
                </div>
            </div>
        </div>

        <div style="overflow-x: auto">
            <table id="datatable" class="table text-nowrap">
                <thead>
                    <tr>
                        {{-- 0  --}} <th>Time</th>
                        {{-- 1  --}} <th>Device ID</th>
                        {{-- 2  --}} <th>Locations</th>
                        {{-- 3  --}} <th>Sub Location</th>
                        {{-- 4  --}} <th>Location-id</th>
                        {{-- 5  --}} <th>Notes</th>
                        {{-- 6  --}} <th>Marked as Normal</th>  {{-- hidden --}}
                        {{-- 7  --}} <th>Noted</th>             {{-- hidden --}}
                        {{-- 8  --}} <th>Updated By</th>
                        {{-- 9  --}} <th>Last Updated</th>
                        {{-- 10 --}} <th>Cams</th>
                        {{-- 11 --}} <th>LatLong</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- /basic datatable -->
@endsection

@push("js")
    <script src="{{ asset("assets/js/vendor/tables/datatables/extensions/pdfmake/pdfmake.min.js") }}"></script>
    <script src="{{ asset("assets/js/vendor/tables/datatables/extensions/pdfmake/vfs_fonts.min.js") }}"></script>
    <script src="{{ asset("assets/js/vendor/tables/datatables/extensions/buttons.min.js") }}"></script>

    <script type="text/javascript">
        $(document).ready(function () {

            // Helper: builds the export URL with all active filter state.
            // Shared by both Export CSV and Export Excel buttons.
            function buildExportUrl(baseUrl) {
                var date     = $('.datepicker-basic').val();
                var search   = $('input[type=search]').val();
                var branch   = $('#branch_filter').val();
                var building = $('#building_filter').val();
                var room     = $('#room_filter').val();
                var order    = datatable.order()[0];
                var colIndex = order[0];
                var dir      = order[1];
                var colName  = datatable.settings().init().columns[colIndex]
                                ? datatable.settings().init().columns[colIndex].name
                                : 'device_status.created_at';

                var url = baseUrl
                    + '?date='    + encodeURIComponent(date)
                    + '&search='  + encodeURIComponent(search)
                    + '&sort='    + encodeURIComponent(colName)
                    + '&dir='     + encodeURIComponent(dir);

                if (branch)   url += '&branches[]='  + encodeURIComponent(branch);
                if (building) url += '&buildings[]=' + encodeURIComponent(building);
                if (room)     url += '&rooms[]='     + encodeURIComponent(room);

                return url;
            }

            const buttons = [
                {
                    text: 'Export CSV',
                    className: 'btn btn-light',
                    action: function () {
                        window.location.href = buildExportUrl('{!! route("admin.status_types.history.export", $id) !!}');
                    }
                },
                {
                    text: 'Export Excel',
                    className: 'btn btn-light',
                    action: function () {
                        window.location.href = buildExportUrl('{!! route("admin.status_types.history.export.excel", $id) !!}');
                    }
                }
            ];

            const datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route("admin.status_types.history", $id) !!}',
                    data: function (d) {
                        d.date = $('.datepicker-basic').val();
                        // Send as arrays to preserve original multi-value filter logic in controller
                        const branch   = $('#branch_filter').val();
                        const building = $('#building_filter').val();
                        const room     = $('#room_filter').val();
                        if (branch)   d.branches  = [branch];
                        if (building) d.buildings = [building];
                        if (room)     d.rooms     = [room];
                    }
                },
                dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
                buttons,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                order: [[0, 'desc']],
                columnDefs: [
                    // "Marked as Normal" and "Noted" exist for export only — not visible in table
                    { targets: [6, 7], visible: false },
                    // Cams and LatLong columns are rendered client-side only
                    { targets: [10, 11], orderable: false, searchable: false }
                ],
                columns: [
                    // 0 — Time
                    {
                        data: 'created_at',
                        name: 'device_status.created_at',
                        render: function (data) {
                            return data ? moment(data).format('YYYY-MM-DD HH:mm:ss') : '-';
                        }
                    },
                    // 1 — Device ID
                    {
                        data: 'device',
                        name: 'device.device_id',
                        defaultContent: '-',
                        render: function (data) {
                            return data ? data.device_id : '-';
                        }
                    },
                    // 2 — Locations (branch)
                    {
                        data: 'device',
                        name: 'device.branch',
                        defaultContent: '-',
                        render: function (data) {
                            return data ? data.branch : '-';
                        }
                    },
                    // 3 — Sub Location (building)
                    {
                        data: 'device',
                        name: 'device.building',
                        defaultContent: '-',
                        render: function (data) {
                            return data ? data.building : '-';
                        }
                    },
                    // 4 — Location-id (room)
                    {
                        data: 'device',
                        name: 'device.room',
                        defaultContent: '-',
                        render: function (data) {
                            return data ? data.room : '-';
                        }
                    },
                    // 5 — Notes
                    {
                        data: 'notes',
                        name: 'device_status.notes',
                        defaultContent: ''
                    },
                    // 6 — Marked as Normal (hidden, plain text for clean export)
                    {
                        data: 'marked_as_read',
                        name: 'device_status.marked_as_read',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return data ? 'TRUE' : 'FALSE';
                        }
                    },
                    // 7 — Noted (hidden, plain text for clean export)
                    {
                        data: 'noted',
                        name: 'device_status.noted',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return data ? 'TRUE' : 'FALSE';
                        }
                    },
                    // 8 — Updated By
                    {
                        data: 'user_name',
                        name: 'user.name',
                        defaultContent: '-'
                    },
                    // 9 — Last Updated
                    {
                        data: 'updated_at',
                        name: 'device_status.updated_at',
                        render: function (data) {
                            return data ? moment(data).format('YYYY-MM-DD HH:mm:ss') : '-';
                        }
                    },
                    // 10 — Cams (display only — export URLs are resolved server-side in export())
                    {
                        data: 'device_log',
                        name: 'device_log',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            var payloads = data ? data.cam_payloads : null;
                            if (!payloads || payloads.length === 0) return 'No Image Available';
                            return '<ul>' + payloads.map(function (cam) {
                                return '<li><a target="_blank" href="/storage/' + cam.file + '">'
                                    + cam.file_name + ' - id: ' + cam.id + '</a></li>';
                            }).join('') + '</ul>';
                        }
                    },
                    // 11 — LatLong
                    {
                        data: 'device_log',
                        name: 'device_log_latlong',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            var payloads = data ? data.cam_payloads : null;
                            if (!payloads || payloads.length === 0) return 'No Latlong Available';
                            return '<ul>' + payloads.map(function (cam) {
                                return '<li><a target="_blank" href="https://www.google.com/maps/search/?api=1&query='
                                    + cam.latlong + '">' + cam.latlong + ' - id: ' + cam.id + '</a></li>';
                            }).join('') + '</ul>';
                        }
                    }
                ]
            });

            // --- Location Filter Cascade Logic ---
            $('#branch_filter').on('change', function () {
                var selectedBranch = $(this).val();
                var buildingFilter = $('#building_filter');
                var roomFilter     = $('#room_filter');

                buildingFilter.val('');
                buildingFilter.find('option').not(':first').hide();
                roomFilter.val('');
                roomFilter.find('option').not(':first').hide();

                if (selectedBranch) {
                    buildingFilter.find('option[data-branch="' + selectedBranch + '"]').show();
                }

                datatable.draw();
            });

            $('#building_filter').on('change', function () {
                var selectedBuilding = $(this).val();
                var roomFilter       = $('#room_filter');

                roomFilter.val('');
                roomFilter.find('option').not(':first').hide();

                if (selectedBuilding) {
                    roomFilter.find('option[data-building="' + selectedBuilding + '"]').show();
                }

                datatable.draw();
            });

            $('#room_filter').on('change', function () {
                datatable.draw();
            });

            // --- Date Range Picker ---
            $('.datepicker-basic').daterangepicker({
                timePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    cancelLabel: 'Clear'
                }
            });

            $('.datepicker-basic').on('apply.daterangepicker', function (ev, picker) {
                $(this).val(
                    picker.startDate.format('YYYY-MM-DD HH:mm:ss') +
                    ' - ' +
                    picker.endDate.format('YYYY-MM-DD HH:mm:ss')
                );
                datatable.draw();
            });

            $('.datepicker-basic').on('cancel.daterangepicker', function () {
                $(this).val('');
                datatable.draw();
            });
        });
    </script>
@endpush
