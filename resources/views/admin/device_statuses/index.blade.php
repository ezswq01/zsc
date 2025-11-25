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
        {{-- Breadcrumbs remain the same --}}
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
                    <input type="text" class="form-control datepicker-basic"
                        placeholder="Pick Start & End Date" name="date">
                </div>
            </div>
        </div>

        <div style="overflow-x:auto">
            <table id="datatable" class="table text-nowrap">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Device ID</th>
                        <th>Status</th>
                        <th>Locations</th>
                        <th>Sub Location</th>
                        <th>Location-id</th>
                        <th>Notes</th>
                        <th>Marked as Normal</th>
                        <th>Noted</th>
                        <th>Updated By</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/buttons.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            // ... (buttons and datatable initialization remains the same)
            const buttons = [{
                text: 'Export CSV',
                className: 'btn btn-light',
                action: function () {
                    let date = $('.datepicker-basic').val();
                    let search = $('input[type=search]').val();
                    let branch = $('#branch_filter').val();
                    let building = $('#building_filter').val();
                    let order = datatable.order()[0];
                    let colIndex = order[0];
                    let dir = order[1];
                    let colName = datatable.settings().init().columns[colIndex].name;

                    let url = '{{ route("admin.device_statuses.export") }}'
                        + '?date=' + encodeURIComponent(date)
                        + '&search=' + encodeURIComponent(search)
                        + '&branch=' + encodeURIComponent(branch)
                        + '&building=' + encodeURIComponent(building)
                        + '&sort=' + colName
                        + '&dir=' + dir;

                    window.location.href = url;
                }
            }];

            const datatable = $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route("admin.device_statuses.index") !!}',
                    data: function(d) {
                        d.date = $('.datepicker-basic').val();
                        d.branch = $('#branch_filter').val();
                        d.building = $('#building_filter').val();
                    }
                },
                dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
                buttons,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                columns: [
                    { data: "created_at", name: "created_at", render: function(data) {
                        return moment(data).format("YYYY-MM-DD HH:mm:ss");
                    }},
                    { data: "device.device_id", name: "device.device_id", defaultContent: "-" },
                    { data: "marked_as_read", name: "marked_as_read", orderable: false, searchable: false, render: function(data, type, row) {
                        return `<div id="mark_${row.id}">${data ? '<i class="ph-check-circle text-success"></i>' : '<i class="ph-question text-danger"></i>'}</div>`;
                    }},
                    { data: "device.branch", name: "device.branch", defaultContent: "-" },
                    { data: "device.building", name: "device.building", defaultContent: "-" },
                    { data: "device.room", name: "device.room", defaultContent: "-" },
                    { data: "notes", name: "notes", defaultContent: "" },
                    { data: "is_normal_state", name: "is_normal_state", visible: false },
                    { data: "noted", name: "noted", visible: false },
                    { data: "user_name", name: "user.name", defaultContent: "-" },
                    { data: "updated_at", name: "updated_at", render: function(data) {
                        return moment(data).format("YYYY-MM-DD HH:mm:ss");
                    }},
                ],
                order: [[0, "desc"]]
            });


            // --- Location Filter Logic ---
            $('#branch_filter').on('change', function() {
                const selectedBranch = $(this).val();
                const buildingFilter = $('#building_filter');
                
                // Reset selection and hide all building options first
                buildingFilter.val('');
                buildingFilter.find('option').not(':first').hide();
                
                if (selectedBranch) {
                    // Show only options that match the selected branch
                    buildingFilter.find('option[data-branch="' + selectedBranch + '"]').show();
                }
                
                datatable.draw();
            });

            $('#building_filter').on('change', function() {
                datatable.draw();
            });

            // --- Date Picker Logic ---
            // ... (Date picker logic remains the same)
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
