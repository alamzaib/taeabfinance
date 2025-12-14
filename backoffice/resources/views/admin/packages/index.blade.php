@extends('adminlte::page')

@section('title', 'Packages Management')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Packages Management</h1>
        </div>
        <div class="col-md-8 text-right">
            <button class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Add New Package"
                onclick="openCreateModal()">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="packages-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Package Details Modal -->
    <div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="packageModalLabel">Package Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <span id="packageModalName"></span></p>
                            <p><strong>Price:</strong> <span id="packageModalPrice"></span></p>
                            <p><strong>Currency:</strong> <span id="packageModalCurrency"></span></p>
                            <p><strong>Period:</strong> <span id="packageModalPeriod"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Popular:</strong> <span id="packageModalPopular"></span></p>
                            <p><strong>Active:</strong> <span id="packageModalActive"></span></p>
                            <p><strong>Created At:</strong> <span id="packageModalCreated"></span></p>
                            <p><strong>Updated At:</strong> <span id="packageModalUpdated"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Features:</strong>
                            <ul id="packageModalFeatures"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Package Modal -->
    <div class="modal fade" id="createPackageModal" tabindex="-1" role="dialog" aria-labelledby="createPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPackageModalLabel">Add Package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="createPackageForm">
                        @csrf
                        <div class="form-group">
                            <label for="create_name">Package Name</label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="create_price">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="create_price" name="price" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="create_currency">Currency</label>
                                    <input type="text" class="form-control" id="create_currency" name="currency" value="USD" maxlength="3" required>
                                    <small class="form-text text-muted">3-letter code</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="create_period">Period</label>
                                    <select class="form-control" id="create_period" name="period" required>
                                        <option value="month">Monthly</option>
                                        <option value="year">Yearly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Features</label>
                            <div id="create-features-container"></div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addFeature('create-features-container')">
                                <i class="fas fa-plus"></i> Add Feature
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="popular" id="create_popular" value="1">
                                        <label class="form-check-label" for="create_popular">Mark as Popular</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="active" id="create_active" value="1" checked>
                                        <label class="form-check-label" for="create_active">Active</label>
                                    </div>
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

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1" role="dialog" aria-labelledby="editPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPackageModalLabel">Edit Package</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editPackageForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_id" name="id">
                        <div class="form-group">
                            <label for="edit_name">Package Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_price">Price</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_price" name="price" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_currency">Currency</label>
                                    <input type="text" class="form-control" id="edit_currency" name="currency" maxlength="3" required>
                                    <small class="form-text text-muted">3-letter code</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_period">Period</label>
                                    <select class="form-control" id="edit_period" name="period" required>
                                        <option value="month">Monthly</option>
                                        <option value="year">Yearly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Features</label>
                            <div id="edit-features-container"></div>
                            <button type="button" class="btn btn-sm btn-secondary mt-2" onclick="addFeature('edit-features-container')">
                                <i class="fas fa-plus"></i> Add Feature
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="popular" id="edit_popular" value="1">
                                        <label class="form-check-label" for="edit_popular">Mark as Popular</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="active" id="edit_active" value="1">
                                        <label class="form-check-label" for="edit_active">Active</label>
                                    </div>
                                </div>
                            </div>
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
@stop

@section('js')
    @stack('scripts')
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script>
        // Initialize Material UI tooltips
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        const csrfToken = '{{ csrf_token() }}';

        var table = new Tabulator("#packages-table", {
            ajaxURL: "{{ route('packages.index') }}",
            ajaxConfig: "GET",
            theme: "bootstrap4",
            height: "600px",
            layout: "fitDataStretch",
            pagination: true,
            paginationSize: 20,
            rowDblClick: function(e, row) {
                e.preventDefault();
                e.stopPropagation();
                if (e.target.closest('button') || e.target.closest('a')) {
                    return;
                }
                var packageId = row.getData().id;
                showPackageDetails(packageId);
            },
            columns: [
                {title: "ID", field: "id", width: 80},
                {
                    title: "Name",
                    field: "name",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var packageId = cell.getRow().getData().id;
                        showPackageDetails(packageId);
                    }
                },
                {title: "Price", field: "price", formatter: "money", formatterParams: {symbol: "$", precision: 2}},
                {title: "Period", field: "period"},
                {title: "Popular", field: "popular", formatter: "tickCross"},
                {title: "Active", field: "active", formatter: "tickCross"},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var id = cell.getRow().getData().id;
                        return '<button class="btn btn-sm btn-warning" data-toggle="tooltip" data-placement="top" title="Edit" onclick="event.stopPropagation(); openEditModal(' + id + ');"><i class="fas fa-edit"></i></button> ' +
                               '<button class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deletePackage(' + id + ')" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
        });

        function showPackageDetails(packageId) {
            fetch('/backoffice/packages/' + packageId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var pkg = data.data.package;
                        $('#packageModalName').text(pkg.name);
                        $('#packageModalPrice').text(pkg.currency + ' ' + parseFloat(pkg.price).toFixed(2));
                        $('#packageModalCurrency').text(pkg.currency);
                        $('#packageModalPeriod').text(pkg.period.charAt(0).toUpperCase() + pkg.period.slice(1));
                        $('#packageModalPopular').html(pkg.popular ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>');
                        $('#packageModalActive').html(pkg.active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>');
                        $('#packageModalCreated').text(new Date(pkg.created_at).toLocaleDateString());
                        $('#packageModalUpdated').text(new Date(pkg.updated_at).toLocaleDateString());
                        
                        var featuresList = $('#packageModalFeatures');
                        featuresList.empty();
                        if (pkg.features && pkg.features.length > 0) {
                            pkg.features.forEach(function(feature) {
                                featuresList.append('<li>' + feature + '</li>');
                            });
                        } else {
                            featuresList.append('<li class="text-muted">No features listed</li>');
                        }
                        
                        $('#packageModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching package details:', error);
                    if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                        showToast.error('Error loading package details');
                    } else {
                        alert('Error loading package details');
                    }
                });
        }

        function openCreateModal() {
            $('#createPackageForm')[0].reset();
            $('#create-features-container').empty();
            $('#create_active').prop('checked', true);
            $('#createPackageModal').modal('show');
        }

        function addFeature(containerId) {
            const featureInput = `
                <div class="input-group mb-2">
                    <input type="text" name="features[]" class="form-control" placeholder="Enter a feature">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#' + containerId).append(featureInput);
        }

        function removeFeature(button) {
            $(button).closest('.input-group').remove();
        }

        function submitCreate() {
            const form = document.getElementById('createPackageForm');
            const formData = new FormData(form);
            
            // Handle checkbox values properly
            formData.set('popular', $('#create_popular').is(':checked') ? '1' : '0');
            formData.set('active', $('#create_active').is(':checked') ? '1' : '0');
            
            fetch('/backoffice/packages', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                        showToast.success(data.message || 'Package created successfully');
                    } else {
                        alert(data.message || 'Package created successfully');
                    }
                    $('#createPackageModal').modal('hide');
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
                            showToast.error(data.message || 'Error creating package');
                        } else {
                            alert(data.message || 'Error creating package');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error creating package:', error);
                if (typeof handleAjaxError === 'function') {
                    handleAjaxError(error, 'Error creating package');
                } else {
                    alert('Error creating package: ' + (error.message || 'Unknown error'));
                }
            });
        }

        function openEditModal(packageId) {
            fetch('/backoffice/packages/' + packageId + '/edit', {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    var pkg = data.data.package;
                    $('#edit_id').val(pkg.id);
                    $('#edit_name').val(pkg.name);
                    $('#edit_price').val(pkg.price);
                    $('#edit_currency').val(pkg.currency);
                    $('#edit_period').val(pkg.period);
                    $('#edit_popular').prop('checked', pkg.popular);
                    $('#edit_active').prop('checked', pkg.active);
                    
                    // Populate features
                    $('#edit-features-container').empty();
                    if (pkg.features && pkg.features.length > 0) {
                        pkg.features.forEach(function(feature) {
                            const featureInput = `
                                <div class="input-group mb-2">
                                    <input type="text" name="features[]" class="form-control" value="${feature}" placeholder="Enter a feature">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger" onclick="removeFeature(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            `;
                            $('#edit-features-container').append(featureInput);
                        });
                    }
                    
                    $('#editPackageModal').modal('show');
                }
            })
            .catch(error => {
                console.error('Error fetching package for edit:', error);
                if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                    showToast.error('Error loading package for edit');
                } else {
                    alert('Error loading package for edit');
                }
            });
        }

        function submitEdit() {
            const form = document.getElementById('editPackageForm');
            const formData = new FormData(form);
            const packageId = $('#edit_id').val();

            // Handle checkbox values properly
            formData.set('popular', $('#edit_popular').is(':checked') ? '1' : '0');
            formData.set('active', $('#edit_active').is(':checked') ? '1' : '0');

            fetch('/backoffice/packages/' + packageId, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                        showToast.success(data.message || 'Package updated successfully');
                    } else {
                        alert(data.message || 'Package updated successfully');
                    }
                    $('#editPackageModal').modal('hide');
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
                            showToast.error(data.message || 'Error updating package');
                        } else {
                            alert(data.message || 'Error updating package');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error updating package:', error);
                handleAjaxError(error, 'Error updating package');
            });
        }

        function deletePackage(id) {
            showConfirm('Are you sure you want to delete this package?', function() {
                // Use FormData with method spoofing for DELETE (similar to PUT)
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                
                fetch('/backoffice/packages/' + id, {
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
                        // If response is not ok, try to parse error
                        return response.json().then(err => {
                            throw { response: { data: err, status: response.status } };
                        }).catch(() => {
                            throw { response: { data: { message: 'Server error: ' + response.status }, status: response.status } };
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (typeof showToast !== 'undefined' && showToast && showToast.success) {
                            showToast.success(data.message || 'Package deleted successfully');
                        } else {
                            alert(data.message || 'Package deleted successfully');
                        }
                        table.replaceData();
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                            showToast.error(data.message || 'Error deleting package');
                        } else {
                            alert(data.message || 'Error deleting package');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error deleting package:', error);
                    if (typeof handleAjaxError === 'function') {
                        handleAjaxError(error, 'Error deleting package');
                    } else {
                        alert('Error deleting package: ' + (error.message || 'Unknown error'));
                    }
                });
            }, 'Delete Package', 'Delete', 'Cancel');
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop


