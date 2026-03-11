@extends('admin.layout.main')

@push('header')
    <div class="page-header page-header-light">
        <div class="page-header-content d-lg-flex">
            <div class="d-flex">
                <h4 class="page-title mb-0">
                    Device Statuses - <span class="fw-normal">All</span>
                </h4>
            </div>
        </div>

        <div class="page-header-content d-lg-flex">
            <div class="d-flex">
                <div class="breadcrumb py-2">
                    <a class="breadcrumb-item" href="/admin/dashboard"><i class="ph-house"></i></a>
                    <a class="breadcrumb-item" href="#">Device Statuses</a>
                    <span class="breadcrumb-item active">All</span>
                </div>
                <a class="btn btn-light align-self-center collapsed d-lg-none border-transparent rounded-pill p-0 ms-auto"
                    data-bs-toggle="collapse" href="#breadcrumb_elements">
                    <i class="ph-caret-down collapsible-indicator ph-sm m-1"></i>
                </a>
            </div>
        </div>
    </div>
@endpush

@section('content')
    <!-- Basic datatable -->
    <div class="card shadow-none">
        <div class="card-header">
            <h5 class="mb-0">Device Statuses</h5>
        </div>

        <div class="card-header">
            <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between">
                {{-- Location Filters --}}
                <div class="d-flex flex-column flex-lg-row gap-2">
                    <div class="">
                        <select class="form-select" id="branch_filter">
                            <option value="">All Locations</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch }}">{{ $branch->branch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="">
                        <select class="form-select" id="building_filter">
                            <option value="">All Sub-locations</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->building }}" data-branch="{{ $building->branch }}" style="display: none;">{{ $building->building }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="">
                    <input type="text" class="form-control datepicker-basic @error('date') is-invalid @enderror"
                        placeholder="Pick Start & End Date" name="date">
                </div>
            </div>
        </div>

        <div style="overflow-x: auto">
            <table id="datatable" class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Device ID</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Sub Location</th>
                        <th>Location-id</th>
                        <th>Notes</th>
                        <th>Is Normal State</th>
                        <th>Noted</th>
                        <th>Updated By</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- /basic datatable -->
@endsection

@push('js')
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/buttons.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            // Helper: build the export URL with all active filter state
            function buildExportUrl(baseUrl) {
                let date     = $('.datepicker-basic').val();
                let search   = $('input[type=search]').val();
                let branch   = $('#branch_filter').val();
                let building = $('#building_filter').val();
                let order    = datatable.order()[0];
                let colIndex = order[0];
                let dir      = order[1];
                let colName  = datatable.settings().init().columns[colIndex].name;

                return baseUrl
                    + '?date='     + encodeURIComponent(date)
                    + '&search='   + encodeURIComponent(search)
                    + '&branch='   + encodeURIComponent(branch)
                    + '&building=' + encodeURIComponent(building)
                    + '&sort='     + encodeURIComponent(colName)
                    + '&dir='      + encodeURIComponent(dir);
            }

            const buttons = [
                {
                    text: 'Export CSV',
                    className: 'btn btn-light',
                    action: function () {
                        window.location.href = buildExportUrl('{{ route("admin.device_statuses.export") }}');
                    }
                },
                {
                    text: 'Export Excel',
                    className: 'btn btn-light',
                    action: function () {
                        window.location.href = buildExportUrl('{{ route("admin.device_statuses.export.excel") }}');
                    }
                }
            ];

            const datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route("admin.device_statuses.index") !!}',
                    data: function(d) {
                        d.date     = $('.datepicker-basic').val();
                        d.branch   = $('#branch_filter').val();
                        d.building = $('#building_filter').val();
                    }
                },
                dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
                buttons,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                columns: [
                    { data: 'created_at',        name: 'created_at', render: function(data) {
                        return moment(data).format('YYYY-MM-DD HH:mm:ss');
                    }},
                    { data: 'device.device_id',  name: 'device.device_id', defaultContent: '-' },
                    { data: 'marked_as_read',    name: 'marked_as_read', orderable: false, searchable: false, render: function(data, type, row) {
                        return `<div id="mark_${row.id}">${data
                            ? '<i class="ph-check-circle text-success"></i>'
                            : '<i class="ph-question text-danger"></i>'
                        }</div>`;
                    }},
                    { data: 'device.branch',     name: 'device.branch',   defaultContent: '-' },
                    { data: 'device.building',   name: 'device.building', defaultContent: '-' },
                    { data: 'device.room',       name: 'device.room',     defaultContent: '-' },
                    { data: 'notes',             name: 'notes',           defaultContent: '' },
                    { data: 'is_normal_state',   name: 'is_normal_state', visible: false },
                    { data: 'noted',             name: 'noted',           visible: false },
                    { data: 'user_name',         name: 'user.name',       defaultContent: '-' },
                    { data: 'updated_at',        name: 'updated_at', render: function(data) {
                        return moment(data).format('YYYY-MM-DD HH:mm:ss');
                    }},
                ],
                order: [[0, 'desc']]
            });

            // --- Location Filter Logic ---
            $('#branch_filter').on('change', function() {
                const selectedBranch = $(this).val();
                const buildingFilter = $('#building_filter');

                buildingFilter.val('');
                buildingFilter.find('option').not(':first').hide();

                if (selectedBranch) {
                    buildingFilter.find('option[data-branch="' + selectedBranch + '"]').show();
                }

                datatable.draw();
            });

            $('#building_filter').on('change', function() {
                datatable.draw();
            });

            // --- Date Picker Logic ---
            $('.datepicker-basic').daterangepicker({
                timePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    cancelLabel: 'Clear'
                }
            });

            $('.datepicker-basic').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
                datatable.draw();
            });

            $('.datepicker-basic').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                datatable.draw();
            });
        });
    </script>
@endpush
