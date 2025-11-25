@extends('admin.layout.main')

@push('header')
    <div class="page-header page-header-light">
        <div class="page-header-content d-lg-flex">
            <div class="d-flex">
                <h4 class="page-title mb-0">
                    Log and Report - <span class="fw-normal">All</span>
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
            <h5 class="mb-0">Log and Report</h5>
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

        <table id="datatable" class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Device ID</th>
                    <th>Location</th>
                    <th>Sub Location</th>
                    <th>Command</th>
                    <th>Type</th>
                    <th>Time</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@push('js')

    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/pdfmake/vfs_fonts.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendor/tables/datatables/extensions/buttons.min.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
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

                    let url = '{{ route("admin.device_logs.export") }}'
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
                    url: '{!! route("admin.device_logs.index") !!}',
                    data: function (d) {
                        d.date = $('.datepicker-basic').val();
                        d.branch = $('#branch_filter').val();
                        d.building = $('#building_filter').val();
                    }
                },
                autoWidth: false,
                dom: '<"datatable-header"fBl><"datatable-scroll"t><"datatable-footer"ip>',
                buttons,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 10,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'device_id', name: 'device.device_id' },
                    { data: 'branch', name: 'device.branch' },
                    { data: 'building', name: 'device.building' },
                    { data: 'value', name: 'value' },
                    { data: 'type', name: 'type' },
                    { data: 'created_at', name: 'created_at' }
                ],
                order: [[6, 'desc']] // Order by the 'Time' column
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
