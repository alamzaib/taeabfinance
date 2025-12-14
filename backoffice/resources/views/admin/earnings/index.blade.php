@extends('adminlte::page')

@section('title', 'Earnings Management')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Earnings Management</h1>
        </div>
        <div class="col-md-8 text-right">
            <button class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Export to Excel"
                onclick="exportToExcel()">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button class="btn btn-info" data-toggle="tooltip" data-placement="top" title="Export to CSV"
                onclick="exportToCSV()">
                <i class="fas fa-file-csv"></i> Export CSV
            </button>
            <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Add New Earning"
                onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Add Earning
            </button>
        </div>
    </div>
@stop

@section('content')
    <!-- Filters -->
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
            <form id="filter-form" class="form-row">
                <div class="form-group col-md-3">
                    <label for="filter-user">User</label>
                    <select id="filter-user" class="form-control">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="filter-type">Type</label>
                    <select id="filter-type" class="form-control">
                        <option value="">All Types</option>
                        <option value="dividend">Dividend</option>
                        <option value="interest">Interest</option>
                        <option value="profit">Profit</option>
                        <option value="bonus">Bonus</option>
                        <option value="referral">Referral</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="filter-start-date">Start Date</label>
                    <input type="date" id="filter-start-date" class="form-control">
                </div>
                <div class="form-group col-md-2">
                    <label for="filter-end-date">End Date</label>
                    <input type="date" id="filter-end-date" class="form-control">
                </div>
                <div class="form-group col-md-1 align-self-end">
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div id="earnings-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Earning Details Modal -->
    <div class="modal fade" id="earningModal" tabindex="-1" role="dialog" aria-labelledby="earningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="earningModalLabel">Earning Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User:</strong> <span id="earningModalUser"></span></p>
                            <p><strong>Email:</strong> <span id="earningModalEmail"></span></p>
                            <p><strong>Package:</strong> <span id="earningModalPackage"></span></p>
                            <p><strong>Type:</strong> <span id="earningModalType"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Amount:</strong> <span id="earningModalAmount"></span></p>
                            <p><strong>Status:</strong> <span id="earningModalStatus"></span></p>
                            <p><strong>Earned Date:</strong> <span id="earningModalDate"></span></p>
                            <p><strong>Created At:</strong> <span id="earningModalCreated"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p id="earningModalDescription" class="mt-2"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Earning Modal -->
    <div class="modal fade" id="createEarningModal" tabindex="-1" role="dialog" aria-labelledby="createEarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createEarningModalLabel">Add New Earning</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createEarningForm">
                        @csrf
                        <div class="form-group">
                            <label for="create_user_id">User <span class="text-danger">*</span></label>
                            <select class="form-control" id="create_user_id" name="user_id" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="create_package_id">Package</label>
                            <select class="form-control" id="create_package_id" name="package_id">
                                <option value="">No Package</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_type">Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="create_type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="dividend">Dividend</option>
                                        <option value="interest">Interest</option>
                                        <option value="profit">Profit</option>
                                        <option value="bonus">Bonus</option>
                                        <option value="referral">Referral</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="create_status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="completed" selected>Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="create_description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="create_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="create_amount">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="create_amount" name="amount" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="create_currency">Currency <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="create_currency" name="currency" value="USD" maxlength="3" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="create_earned_date">Earned Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="create_earned_date" name="earned_date" required>
                                </div>
                            </div>
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

    <!-- Edit Earning Modal -->
    <div class="modal fade" id="editEarningModal" tabindex="-1" role="dialog" aria-labelledby="editEarningModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEarningModalLabel">Edit Earning</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editEarningForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_user_id">User <span class="text-danger">*</span></label>
                            <select class="form-control" id="edit_user_id" name="user_id" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_package_id">Package</label>
                            <select class="form-control" id="edit_package_id" name="package_id">
                                <option value="">No Package</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_type">Type <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="dividend">Dividend</option>
                                        <option value="interest">Interest</option>
                                        <option value="profit">Profit</option>
                                        <option value="bonus">Bonus</option>
                                        <option value="referral">Referral</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control" id="edit_status" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_description">Description <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_amount">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_amount" name="amount" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_currency">Currency <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_currency" name="currency" maxlength="3" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="edit_earned_date">Earned Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="edit_earned_date" name="earned_date" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitEdit()">Save Changes</button>
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
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';

        var table = new Tabulator("#earnings-table", {
            ajaxURL: "{{ route('earnings.index') }}",
            ajaxConfig: "GET",
            ajaxResponse: function(url, params, response) {
                return Array.isArray(response) ? response : (response.data || []);
            },
            placeholder: "No Earnings Found",
            theme: "bootstrap4",
            height: "600px",
            layout: "fitDataStretch",
            pagination: true,
            paginationSize: 20,
            paginationSizeSelector: [10, 20, 50, 100],
            rowDblClick: function(e, row) {
                console.log('Row double-clicked', row.getData());
                e.preventDefault();
                e.stopPropagation();
                // Don't trigger on action buttons
                if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                    console.log('Double-click ignored - clicked on button/link');
                    return;
                }
                var earningId = row.getData().id;
                console.log('Showing earning details for ID:', earningId);
                if (earningId) {
                    showEarningDetails(earningId);
                } else {
                    console.error('No earning ID found');
                }
            },
            columns: [
                {
                    title: "User", 
                    field: "user_name", 
                    formatter: function(cell) {
                        return cell.getValue() + (cell.getData().user_email ? ' (' + cell.getData().user_email + ')' : '');
                    },
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var earningId = cell.getRow().getData().id;
                        if (earningId) {
                            showEarningDetails(earningId);
                        }
                    }
                },
                {
                    title: "Type", 
                    field: "type", 
                    formatter: function(cell) {
                        var type = cell.getValue();
                        var colors = {
                            'dividend': 'primary',
                            'interest': 'info',
                            'profit': 'success',
                            'bonus': 'warning',
                            'referral': 'secondary',
                            'other': 'dark'
                        };
                        return '<span class="badge badge-' + (colors[type] || 'secondary') + '">' + type.charAt(0).toUpperCase() + type.slice(1) + '</span>';
                    },
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var earningId = cell.getRow().getData().id;
                        if (earningId) {
                            showEarningDetails(earningId);
                        }
                    }
                },
                {
                    title: "Description", 
                    field: "description", 
                    width: 200,
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var earningId = cell.getRow().getData().id;
                        if (earningId) {
                            showEarningDetails(earningId);
                        }
                    }
                },
                {
                    title: "Package", 
                    field: "package_name",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var earningId = cell.getRow().getData().id;
                        if (earningId) {
                            showEarningDetails(earningId);
                        }
                    }
                },
                {
                    title: "Amount",
                    field: "amount",
                    formatter: function(cell) {
                        var data = cell.getRow().getData();
                        return data.currency + ' ' + parseFloat(data.amount).toFixed(2);
                    },
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var earningId = cell.getRow().getData().id;
                        if (earningId) {
                            showEarningDetails(earningId);
                        }
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    formatter: function(cell) {
                        var status = cell.getValue();
                        var colors = {
                            'pending': 'warning',
                            'completed': 'success',
                            'cancelled': 'danger'
                        };
                        return '<span class="badge badge-' + (colors[status] || 'secondary') + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                    }
                },
                {title: "Earned Date", field: "earned_date", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD", outputFormat: "MM/DD/YYYY"}},
                {title: "Created At", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var id = cell.getRow().getData().id;
                        return '<button class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Edit" onclick="event.stopPropagation(); openEditModal(' + id + ');"><i class="fas fa-edit"></i></button> ' +
                               '<button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteEarning(' + id + ')" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
        });

        function buildUrl() {
            let url = "{{ route('earnings.index') }}";
            const params = new URLSearchParams();

            const userId = $('#filter-user').val();
            if (userId) params.append('user_id', userId);

            const type = $('#filter-type').val();
            if (type) params.append('type', type);

            const status = $('#filter-status').val();
            if (status) params.append('status', status);

            const startDate = $('#filter-start-date').val();
            if (startDate) params.append('start_date', startDate);

            const endDate = $('#filter-end-date').val();
            if (endDate) params.append('end_date', endDate);

            if (params.toString()) {
                url += '?' + params.toString();
            }
            return url;
        }

        function applyFilters() {
            table.setData(buildUrl());
        }

        function resetFilters() {
            $('#filter-form')[0].reset();
            applyFilters();
        }

        function showEarningDetails(earningId) {
            console.log('showEarningDetails called with ID:', earningId);
            if (!earningId) {
                console.error('No earning ID provided');
                return;
            }
            
            // Show loading state
            $('#earningModalUser').text('Loading...');
            $('#earningModalEmail').text('');
            $('#earningModalPackage').text('');
            $('#earningModalType').html('');
            $('#earningModalAmount').text('');
            $('#earningModalStatus').html('');
            $('#earningModalDate').text('');
            $('#earningModalCreated').text('');
            $('#earningModalDescription').text('');
            
            console.log('Fetching earning details from:', '/backoffice/earnings/' + earningId);
            fetch('/backoffice/earnings/' + earningId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.data && data.data.earning) {
                    var earning = data.data.earning;
                    $('#earningModalUser').text(earning.user_name || 'N/A');
                    $('#earningModalEmail').text(earning.user_email || 'N/A');
                    $('#earningModalPackage').text(earning.package_name || 'N/A');
                    $('#earningModalType').html('<span class="badge badge-primary">' + (earning.type ? earning.type.charAt(0).toUpperCase() + earning.type.slice(1) : 'N/A') + '</span>');
                    $('#earningModalAmount').text((earning.currency || 'USD') + ' ' + parseFloat(earning.amount || 0).toFixed(2));
                    var statusBadge = earning.status === 'completed' ? 'success' : (earning.status === 'pending' ? 'warning' : 'danger');
                    $('#earningModalStatus').html('<span class="badge badge-' + statusBadge + '">' + (earning.status ? earning.status.charAt(0).toUpperCase() + earning.status.slice(1) : 'N/A') + '</span>');
                    $('#earningModalDate').text(earning.earned_date ? new Date(earning.earned_date).toLocaleDateString() : 'N/A');
                    $('#earningModalCreated').text(earning.created_at ? new Date(earning.created_at).toLocaleDateString() : 'N/A');
                    $('#earningModalDescription').text(earning.description || 'No description');
                    
                    // Show modal using Bootstrap
                    console.log('Attempting to show modal');
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        console.log('Using jQuery modal');
                        $('#earningModal').modal('show');
                        // Force show if modal doesn't appear
                        setTimeout(function() {
                            if (!$('#earningModal').hasClass('show')) {
                                console.log('Modal not showing, forcing display');
                                $('#earningModal').addClass('show').css('display', 'block');
                                $('body').addClass('modal-open');
                                $('.modal-backdrop').remove();
                                $('body').append('<div class="modal-backdrop fade show"></div>');
                            }
                        }, 100);
                    } else {
                        console.log('jQuery/Bootstrap not available, using fallback');
                        // Fallback if jQuery/Bootstrap not loaded
                        var modal = document.getElementById('earningModal');
                        if (modal) {
                            modal.style.display = 'block';
                            modal.classList.add('show');
                            document.body.classList.add('modal-open');
                        }
                    }
                } else {
                    throw new Error('Invalid response data');
                }
            })
            .catch(error => {
                console.error('Error fetching earning details:', error);
                if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                    showToast.error('Error loading earning details: ' + (error.message || 'Unknown error'));
                } else {
                    alert('Error loading earning details: ' + (error.message || 'Unknown error'));
                }
            });
        }

        function openCreateModal() {
            $('#createEarningForm')[0].reset();
            $('#create_currency').val('USD');
            $('#create_status').val('completed');
            $('#create_earned_date').val(new Date().toISOString().split('T')[0]);
            $('#createEarningModal').modal('show');
        }

        function submitCreate() {
            const form = document.getElementById('createEarningForm');
            const formData = new FormData(form);

            fetch('/backoffice/earnings', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw { response: { data: err, status: response.status } };
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                        showToast.success(data.message || 'Earning created successfully');
                    } else {
                        alert(data.message || 'Earning created successfully');
                    }
                    $('#createEarningModal').modal('hide');
                    table.replaceData();
                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    if (data.errors) {
                        if (typeof handleValidationErrors === 'function') {
                            handleValidationErrors(data.errors);
                        } else {
                            alert('Validation errors occurred');
                        }
                    } else {
                        if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                            showToast.error(data.message || 'Error creating earning');
                        } else {
                            alert(data.message || 'Error creating earning');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error creating earning:', error);
                if (typeof handleAjaxError === 'function') {
                    handleAjaxError(error, 'Error creating earning');
                } else {
                    alert('Error creating earning: ' + (error.message || 'Unknown error'));
                }
            });
        }

        function openEditModal(earningId) {
            fetch('/backoffice/earnings/' + earningId + '/edit', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var earning = data.data.earning;
                    $('#edit_id').val(earning.id);
                    $('#edit_user_id').val(earning.user_id);
                    $('#edit_package_id').val(earning.package_id || '');
                    $('#edit_type').val(earning.type);
                    $('#edit_status').val(earning.status);
                    $('#edit_description').val(earning.description);
                    $('#edit_amount').val(earning.amount);
                    $('#edit_currency').val(earning.currency);
                    $('#edit_earned_date').val(earning.earned_date);
                    $('#editEarningModal').modal('show');
                }
            })
            .catch(error => {
                console.error('Error fetching earning for edit:', error);
                if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                    showToast.error('Error loading earning for edit');
                } else {
                    alert('Error loading earning for edit');
                }
            });
        }

        function submitEdit() {
            const form = document.getElementById('editEarningForm');
            const formData = new FormData(form);
            const earningId = $('#edit_id').val();

            formData.append('_method', 'PUT');

            fetch('/backoffice/earnings/' + earningId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw { response: { data: err, status: response.status } };
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                        showToast.success(data.message || 'Earning updated successfully');
                    } else {
                        alert(data.message || 'Earning updated successfully');
                    }
                    $('#editEarningModal').modal('hide');
                    table.replaceData();
                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    if (data.errors) {
                        if (typeof handleValidationErrors === 'function') {
                            handleValidationErrors(data.errors);
                        } else {
                            alert('Validation errors occurred');
                        }
                    } else {
                        if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                            showToast.error(data.message || 'Error updating earning');
                        } else {
                            alert(data.message || 'Error updating earning');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error updating earning:', error);
                if (typeof handleAjaxError === 'function') {
                    handleAjaxError(error, 'Error updating earning');
                } else {
                    alert('Error updating earning: ' + (error.message || 'Unknown error'));
                }
            });
        }

        function deleteEarning(id) {
            showConfirm('Are you sure you want to delete this earning?', function() {
                const formData = new FormData();
                formData.append('_method', 'DELETE');

                fetch('/backoffice/earnings/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw { response: { data: err, status: response.status } };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                            showToast.success(data.message || 'Earning deleted successfully');
                        } else {
                            alert(data.message || 'Earning deleted successfully');
                        }
                        table.replaceData();
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                            showToast.error(data.message || 'Error deleting earning');
                        } else {
                            alert(data.message || 'Error deleting earning');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error deleting earning:', error);
                    if (typeof handleAjaxError === 'function') {
                        handleAjaxError(error, 'Error deleting earning');
                    } else {
                        alert('Error deleting earning: ' + (error.message || 'Unknown error'));
                    }
                });
            }, 'Delete Earning', 'Delete', 'Cancel');
        }

        function exportToExcel() {
            let url = "{{ route('earnings.export') }}";
            const params = new URLSearchParams();

            const userId = $('#filter-user').val();
            if (userId) params.append('user_id', userId);

            const type = $('#filter-type').val();
            if (type) params.append('type', type);

            const status = $('#filter-status').val();
            if (status) params.append('status', status);

            const startDate = $('#filter-start-date').val();
            if (startDate) params.append('start_date', startDate);

            const endDate = $('#filter-end-date').val();
            if (endDate) params.append('end_date', endDate);

            params.append('format', 'excel');

            if (params.toString()) {
                url += '?' + params.toString();
            }
            
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.earnings) {
                    const ws = XLSX.utils.json_to_sheet(data.data.earnings);
                    const wb = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(wb, ws, "Earnings");
                    const filename = 'earnings_' + new Date().toISOString().split('T')[0] + '.xlsx';
                    XLSX.writeFile(wb, filename);
                    if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                        showToast.success('Earnings exported successfully');
                    }
                } else {
                    if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                        showToast.error('Failed to export earnings');
                    } else {
                        alert('Failed to export earnings');
                    }
                }
            })
            .catch(error => {
                console.error('Error exporting earnings:', error);
                if (typeof handleAjaxError === 'function') {
                    handleAjaxError(error, 'Error exporting earnings');
                } else {
                    alert('Error exporting earnings');
                }
            });
        }

        function exportToCSV() {
            let url = "{{ route('earnings.export') }}";
            const params = new URLSearchParams();

            const userId = $('#filter-user').val();
            if (userId) params.append('user_id', userId);

            const type = $('#filter-type').val();
            if (type) params.append('type', type);

            const status = $('#filter-status').val();
            if (status) params.append('status', status);

            const startDate = $('#filter-start-date').val();
            if (startDate) params.append('start_date', startDate);

            const endDate = $('#filter-end-date').val();
            if (endDate) params.append('end_date', endDate);

            params.append('format', 'csv');

            if (params.toString()) {
                url += '?' + params.toString();
            }
            window.location.href = url;
        }

        // Initialize tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
            
            // Ensure Bootstrap modal is available
            if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                console.log('Bootstrap modal is available');
            } else {
                console.warn('Bootstrap modal may not be available');
            }
        });

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
        
        // Debug: Test double-click
        console.log('Earnings table initialized with double-click support');
    </script>
@stop

