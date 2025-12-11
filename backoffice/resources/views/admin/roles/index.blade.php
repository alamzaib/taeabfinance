@extends('adminlte::page')

@section('title', 'Roles & Permissions')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Roles & Permissions</h1>
        </div>
        <div class="col-md-8 text-right">
            <a href="{{ route('roles.create') }}" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Add New Role">
                <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="roles-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Role Details Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel">Role Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <span id="roleModalName"></span></p>
                            <p><strong>Users Count:</strong> <span id="roleModalUsersCount"></span></p>
                            <p><strong>Created At:</strong> <span id="roleModalCreated"></span></p>
                            <p><strong>Updated At:</strong> <span id="roleModalUpdated"></span></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Permissions:</strong>
                            <div id="roleModalPermissions" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Users with this role:</strong>
                            <div id="roleModalUsers" class="mt-2"></div>
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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script>
        // Initialize Material UI tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        var table = new Tabulator("#roles-table", {
            ajaxURL: "{{ route('roles.index') }}",
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
                var roleId = row.getData().id;
                showRoleDetails(roleId);
            },
            columns: [
                {title: "ID", field: "id", width: 80},
                {
                    title: "Name",
                    field: "name",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var roleId = cell.getRow().getData().id;
                        showRoleDetails(roleId);
                    }
                },
                {title: "Permissions", field: "permissions", formatter: "html"},
                {title: "Users Count", field: "users_count"},
                {title: "Created", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var id = cell.getRow().getData().id;
                        return '<a href="/backoffice/roles/' + id + '/edit" class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Edit" onclick="event.stopPropagation();"><i class="fas fa-edit"></i></a> ' +
                               '<button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteRole(' + id + ')" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
        });

        function showRoleDetails(roleId) {
            fetch('/backoffice/roles/' + roleId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var role = data.data.role;
                        $('#roleModalName').text(role.name);
                        $('#roleModalUsersCount').text(role.users_count);
                        $('#roleModalCreated').text(new Date(role.created_at).toLocaleDateString());
                        $('#roleModalUpdated').text(new Date(role.updated_at).toLocaleDateString());
                        
                        var permissionsDiv = $('#roleModalPermissions');
                        permissionsDiv.empty();
                        if (role.permissions && role.permissions.length > 0) {
                            role.permissions.forEach(function(permission) {
                                permissionsDiv.append('<span class="badge badge-secondary mr-1 mb-1">' + permission.name + '</span>');
                            });
                        } else {
                            permissionsDiv.append('<span class="text-muted">No permissions assigned</span>');
                        }
                        
                        var usersDiv = $('#roleModalUsers');
                        usersDiv.empty();
                        if (role.users && role.users.length > 0) {
                            var usersList = $('<ul class="list-unstyled"></ul>');
                            role.users.forEach(function(user) {
                                usersList.append('<li>' + user.name + ' (' + user.email + ')</li>');
                            });
                            usersDiv.append(usersList);
                        } else {
                            usersDiv.append('<span class="text-muted">No users assigned</span>');
                        }
                        
                        $('#roleModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching role details:', error);
                    showToast.error('Error loading role details');
                });
        }

        function deleteRole(id) {
            showConfirm('Are you sure you want to delete this role?', function() {
                fetch('/backoffice/roles/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast.success(data.message || 'Role deleted successfully');
                        table.replaceData();
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        showToast.error(data.message || 'Error deleting role');
                    }
                })
                .catch(error => {
                    console.error('Error deleting role:', error);
                    handleAjaxError(error, 'Error deleting role');
                });
            }, 'Delete Role', 'Delete', 'Cancel');
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

