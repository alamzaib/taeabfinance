@extends('adminlte::page')

@section('title', 'Activity Logs')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Activity Logs</h1>
        </div>
        <div class="col-md-8 text-right">
            <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Export to Excel"
                onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filters</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>User</label>
                        <select class="form-control" id="filter-user">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Action</label>
                        <select class="form-control" id="filter-action">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}">{{ ucfirst($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Module</label>
                        <select class="form-control" id="filter-module">
                            <option value="">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}">{{ $module }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date From</label>
                        <input type="date" class="form-control" id="filter-date-from">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Date To</label>
                        <input type="date" class="form-control" id="filter-date-to">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-block" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div id="activity-logs-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Activity Log Details Modal -->
    <div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="logModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logModalLabel">Activity Log Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User:</strong> <span id="logModalUser"></span></p>
                            <p><strong>Action:</strong> <span id="logModalAction"></span></p>
                            <p><strong>Module:</strong> <span id="logModalModule"></span></p>
                            <p><strong>Model Type:</strong> <span id="logModalModelType"></span></p>
                            <p><strong>Model ID:</strong> <span id="logModalModelId"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>IP Address:</strong> <span id="logModalIp"></span></p>
                            <p><strong>Route:</strong> <span id="logModalRoute"></span></p>
                            <p><strong>Created At:</strong> <span id="logModalCreated"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p id="logModalDescription" class="mt-2"></p>
                        </div>
                    </div>
                    <div class="row mt-3" id="logModalOldValues" style="display: none;">
                        <div class="col-12">
                            <strong>Old Values:</strong>
                            <pre id="logModalOldValuesContent" class="bg-light p-3 rounded"></pre>
                        </div>
                    </div>
                    <div class="row mt-3" id="logModalNewValues" style="display: none;">
                        <div class="col-12">
                            <strong>New Values:</strong>
                            <pre id="logModalNewValuesContent" class="bg-light p-3 rounded"></pre>
                        </div>
                    </div>
                    <div class="row mt-3" id="logModalRequestData" style="display: none;">
                        <div class="col-12">
                            <strong>Request Data:</strong>
                            <pre id="logModalRequestDataContent" class="bg-light p-3 rounded"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    @include('admin.partials.toastr')
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator.min.css" rel="stylesheet">
    <link href="https://unpkg.com/tabulator-tables@5.5.2/dist/css/tabulator_bootstrap4.min.css" rel="stylesheet">
@stop

@section('js')
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        let currentFilters = {};

        // Build URL with filters
        function buildUrl() {
            let url = "{{ route('activity-logs.index') }}?";
            const params = new URLSearchParams();
            
            if (currentFilters.user_id) params.append('user_id', currentFilters.user_id);
            if (currentFilters.action) params.append('action', currentFilters.action);
            if (currentFilters.module) params.append('module', currentFilters.module);
            if (currentFilters.date_from) params.append('date_from', currentFilters.date_from);
            if (currentFilters.date_to) params.append('date_to', currentFilters.date_to);
            params.append('per_page', 20);
            
            return url + params.toString();
        }

        var table = new Tabulator("#activity-logs-table", {
            ajaxURL: buildUrl(),
            ajaxConfig: "GET",
            theme: "bootstrap4",
            height: "600px",
            layout: "fitDataStretch",
            pagination: true,
            paginationSize: 20,
            paginationSizeSelector: [10, 20, 50, 100],
            rowDblClick: function(e, row) {
                e.preventDefault();
                e.stopPropagation();
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                var logId = row.getData().id;
                showLogDetails(logId);
            },
            columns: [
                {title: "ID", field: "id", width: 80},
                {
                    title: "User",
                    field: "user_name",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var logId = cell.getRow().getData().id;
                        showLogDetails(logId);
                    }
                },
                {
                    title: "Action",
                    field: "action",
                    formatter: function(cell) {
                        var action = cell.getValue();
                        var colors = {
                            'create': 'success',
                            'update': 'info',
                            'delete': 'danger',
                            'view': 'secondary',
                            'login': 'primary',
                            'logout': 'warning'
                        };
                        var color = colors[action] || 'secondary';
                        return '<span class="badge badge-' + color + '">' + action.charAt(0).toUpperCase() + action.slice(1) + '</span>';
                    }
                },
                {title: "Module", field: "module"},
                {title: "Model", field: "model_type"},
                {title: "Description", field: "description", width: 300, formatter: "textarea"},
                {title: "IP Address", field: "ip_address", width: 130},
                {
                    title: "Date",
                    field: "created_at",
                    formatter: "datetime",
                    formatterParams: {
                        inputFormat: "YYYY-MM-DD HH:mm:ss",
                        outputFormat: "MM/DD/YYYY HH:mm"
                    },
                    sorter: "datetime"
                },
                {
                    title: "Actions",
                    formatter: "html",
                    width: 100,
                    formatter: function(cell) {
                        var id = cell.getRow().getData().id;
                        return '<button class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="View Details" onclick="event.stopPropagation(); showLogDetails(' + id + ');"><i class="fas fa-eye"></i></button>';
                    }
                }
            ],
        });

        function applyFilters() {
            currentFilters = {
                user_id: $('#filter-user').val(),
                action: $('#filter-action').val(),
                module: $('#filter-module').val(),
                date_from: $('#filter-date-from').val(),
                date_to: $('#filter-date-to').val(),
            };
            
            table.setData(buildUrl());
        }

        function showLogDetails(logId) {
            fetch('/backoffice/activity-logs/' + logId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var log = data.data.log;
                        $('#logModalUser').text(log.user ? log.user.name + ' (' + log.user.email + ')' : 'System');
                        $('#logModalAction').html('<span class="badge badge-info">' + log.action.charAt(0).toUpperCase() + log.action.slice(1) + '</span>');
                        $('#logModalModule').text(log.module);
                        $('#logModalModelType').text(log.model_type ? log.model_type.split('\\').pop() : 'N/A');
                        $('#logModalModelId').text(log.model_id || 'N/A');
                        $('#logModalIp').text(log.ip_address || 'N/A');
                        $('#logModalRoute').text(log.route || 'N/A');
                        $('#logModalCreated').text(new Date(log.created_at).toLocaleString());
                        $('#logModalDescription').text(log.description || 'N/A');
                        
                        // Show old values if available
                        if (log.old_values && Object.keys(log.old_values).length > 0) {
                            $('#logModalOldValues').show();
                            $('#logModalOldValuesContent').text(JSON.stringify(log.old_values, null, 2));
                        } else {
                            $('#logModalOldValues').hide();
                        }
                        
                        // Show new values if available
                        if (log.new_values && Object.keys(log.new_values).length > 0) {
                            $('#logModalNewValues').show();
                            $('#logModalNewValuesContent').text(JSON.stringify(log.new_values, null, 2));
                        } else {
                            $('#logModalNewValues').hide();
                        }
                        
                        // Show request data if available
                        if (log.request_data && Object.keys(log.request_data).length > 0) {
                            $('#logModalRequestData').show();
                            $('#logModalRequestDataContent').text(JSON.stringify(log.request_data, null, 2));
                        } else {
                            $('#logModalRequestData').hide();
                        }
                        
                        $('#logModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching log details:', error);
                    window.showToast.error('Error loading log details');
                });
        }

        function exportToExcel() {
            // Build export URL with current filters
            let url = "{{ route('activity-logs.export') }}?";
            const params = new URLSearchParams();
            
            if (currentFilters.user_id) params.append('user_id', currentFilters.user_id);
            if (currentFilters.action) params.append('action', currentFilters.action);
            if (currentFilters.module) params.append('module', currentFilters.module);
            if (currentFilters.date_from) params.append('date_from', currentFilters.date_from);
            if (currentFilters.date_to) params.append('date_to', currentFilters.date_to);
            
            url += params.toString();

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Convert to worksheet
                    const ws = XLSX.utils.json_to_sheet(data.data);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Activity Logs");
                    
                    // Generate filename with timestamp
                    const filename = 'activity-logs-' + new Date().toISOString().split('T')[0] + '.xlsx';
                    
                    // Download
                    XLSX.writeFile(wb, filename);
                    window.showToast.success('Activity logs exported successfully');
                } else {
                    window.showToast.error('Error exporting activity logs');
                }
            })
            .catch(error => {
                console.error('Error exporting:', error);
                window.showToast.error('Error exporting activity logs');
            });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

