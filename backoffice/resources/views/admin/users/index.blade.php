@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>User Management</h1>
        </div>
        <div class="col-md-8 text-right">
            <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Add New User"
                onclick="openCreateModal()">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="users-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">User Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <span id="userModalName"></span></p>
                            <p><strong>Email:</strong> <span id="userModalEmail"></span></p>
                            <p><strong>Roles:</strong> <span id="userModalRoles"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created At:</strong> <span id="userModalCreated"></span></p>
                            <p><strong>Updated At:</strong> <span id="userModalUpdated"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        @csrf
                        <div class="form-group">
                            <label for="create_name">Name</label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="create_email">Email</label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="create_password">Password</label>
                            <input type="password" class="form-control" id="create_password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="create_password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="create_password_confirmation"
                                name="password_confirmation" required>
                        </div>
                        <div class="form-group">
                            <label for="create_roles">Roles</label>
                            <select class="form-control" id="create_roles" name="roles[]" multiple>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitCreate()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_password">Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                        </div>
                        <div class="form-group">
                            <label for="edit_password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="edit_password_confirmation"
                                name="password_confirmation">
                        </div>
                        <div class="form-group">
                            <label for="edit_roles">Roles</label>
                            <select class="form-control" id="edit_roles" name="roles[]" multiple>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple roles.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitEdit()">Save</button>
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
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script>
        // Initialize Material UI tooltips
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        const csrfToken = '{{ csrf_token() }}';

        var table = new Tabulator("#users-table", {
            ajaxURL: "{{ route('users.index') }}",
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
                // Ignore dblclicks on buttons
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                var userId = row.getData().id;
                console.log('Double-click detected, userId:', userId);
                showUserDetails(userId);
            },
            columns: [{
                    title: "ID",
                    field: "id",
                    width: 80
                },
                {
                    title: "Name",
                    field: "name",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var userId = cell.getRow().getData().id;
                        console.log('Name cell double-clicked, userId:', userId);
                        showUserDetails(userId);
                    }
                },
                {
                    title: "Email",
                    field: "email",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var userId = cell.getRow().getData().id;
                        console.log('Email cell double-clicked, userId:', userId);
                        showUserDetails(userId);
                    }
                },
                {
                    title: "Roles",
                    field: "roles",
                    formatter: "html"
                },
                {
                    title: "Created",
                    field: "created_at",
                    formatter: "datetime",
                    formatterParams: {
                        inputFormat: "YYYY-MM-DD HH:mm:ss",
                        outputFormat: "MM/DD/YYYY"
                    }
                },
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var id = cell.getRow().getData().id;
                        return '<button class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Edit" onclick="event.stopPropagation(); openEditModal(' +
                            id + ');"><i class="fas fa-edit"></i></button>';
                    }
                }
            ],
        });

        function showUserDetails(userId) {
            console.log('showUserDetails called with userId:', userId);
            fetch('/backoffice/users/' + userId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    if (data.success) {
                        var user = data.data.user;
                        var rolesHtml = user.roles && user.roles.length > 0 ?
                            user.roles.map(role => '<span class="badge badge-info">' + role.name + '</span>').join(
                                ' ') :
                            '<span class="text-muted">No roles assigned</span>';

                        $('#userModalName').text(user.name);
                        $('#userModalEmail').text(user.email);
                        $('#userModalRoles').html(rolesHtml);
                        $('#userModalCreated').text(new Date(user.created_at).toLocaleDateString());
                        $('#userModalUpdated').text(new Date(user.updated_at).toLocaleDateString());
                        $('#userModal').modal('show');
                        console.log('Modal should be shown now');
                    } else {
                        console.error('Data success is false:', data);
                        showToast.error('Error loading user details');
                    }
                })
                .catch(error => {
                    console.error('Error fetching user details:', error);
                    showToast.error('Error loading user details: ' + (error.message || 'Unknown error'));
                });
        }

        function openCreateModal() {
            $('#createUserForm')[0].reset();
            $('#createUserModal').modal('show');
        }

        function submitCreate() {
            const form = document.getElementById('createUserForm');
            const formData = new FormData(form);
            fetch('/backoffice/users', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast.success(data.message || 'User created successfully');
                        $('#createUserModal').modal('hide');
                        table.replaceData();
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        if (data.errors) {
                            handleValidationErrors(data.errors);
                        } else {
                            showToast.error(data.message || 'Error creating user');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error creating user:', error);
                    handleAjaxError(error, 'Error creating user');
                });
        }

        function openEditModal(id) {
            fetch('/backoffice/users/' + id, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data.user;
                        $('#edit_id').val(user.id);
                        $('#edit_name').val(user.name);
                        $('#edit_email').val(user.email);
                        $('#edit_password').val('');
                        $('#edit_password_confirmation').val('');
                        $('#edit_roles').val(user.roles.map(r => r.name));
                        $('#editUserModal').modal('show');
                    } else {
                        showToast.error('Error loading user');
                    }
                })
                .catch(error => {
                    console.error('Error loading user:', error);
                    showToast.error('Error loading user');
                });
        }

        function submitEdit() {
            const form = document.getElementById('editUserForm');
            const formData = new FormData(form);
            const userId = document.getElementById('edit_id').value;
            fetch('/backoffice/users/' + userId, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast.success(data.message || 'User updated successfully');
                        $('#editUserModal').modal('hide');
                        table.replaceData();
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        if (data.errors) {
                            handleValidationErrors(data.errors);
                        } else {
                            showToast.error(data.message || 'Error updating user');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating user:', error);
                    handleAjaxError(error, 'Error updating user');
                });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop
