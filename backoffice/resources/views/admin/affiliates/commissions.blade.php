@extends('adminlte::page')

@section('title', 'Affiliate Commissions')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Affiliate Commissions</h1>
        </div>
        <div class="col-md-8 text-right">
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
            <form id="filter-form" class="form-row">
                <div class="form-group col-md-4">
                    <label for="filter-referrer">Referrer</label>
                    <select id="filter-referrer" class="form-control">
                        <option value="">All Referrers</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group col-md-4 align-self-end">
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply Filters
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
            <div id="commissions-table" style="width: 100%;"></div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> Commissions are automatically generated when referred users complete payments. 
                No commissions will appear until a referred user makes a completed payment.
            </small>
        </div>
    </div>

    <!-- Commission Details Modal -->
    <div class="modal fade" id="commissionModal" tabindex="-1" role="dialog" aria-labelledby="commissionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="commissionModalLabel">Commission Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Referrer:</strong> <span id="commissionModalReferrer">-</span></p>
                            <p><strong>Referrer Email:</strong> <span id="commissionModalReferrerEmail">-</span></p>
                            <p><strong>Referred User:</strong> <span id="commissionModalReferred">-</span></p>
                            <p><strong>Referred Email:</strong> <span id="commissionModalReferredEmail">-</span></p>
                            <p><strong>Payment ID:</strong> <span id="commissionModalPaymentId">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Commission Type:</strong> <span id="commissionModalType">-</span></p>
                            <p><strong>Commission Rate:</strong> <span id="commissionModalRate">-</span></p>
                            <p><strong>Commission Amount:</strong> <span id="commissionModalAmount">-</span></p>
                            <p><strong>Status:</strong> <span id="commissionModalStatus">-</span></p>
                            <p><strong>Paid At:</strong> <span id="commissionModalPaidAt">-</span></p>
                            <p><strong>Created At:</strong> <span id="commissionModalCreated">-</span></p>
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
    <script>
        const csrfToken = '{{ csrf_token() }}';

        var table = new Tabulator("#commissions-table", {
            ajaxURL: "{{ route('affiliates.commissions') }}",
            ajaxConfig: "GET",
            ajaxResponse: function(url, params, response) {
                // Handle array response directly
                return Array.isArray(response) ? response : (response.data || []);
            },
            placeholder: "No Commissions Found",
            theme: "bootstrap4",
            height: "600px",
            layout: "fitDataStretch",
            pagination: true,
            paginationSize: 20,
            paginationSizeSelector: [10, 20, 50, 100],
            rowDblClick: function(e, row) {
                e.preventDefault();
                e.stopPropagation();
                if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                    return;
                }
                var commissionId = row.getData().id;
                if (commissionId) {
                    showCommissionDetails(commissionId);
                }
            },
            columns: [
                {
                    title: "Referrer", 
                    field: "referrer_name", 
                    formatter: function(cell) {
                        return cell.getValue() + (cell.getData().referrer_email ? ' (' + cell.getData().referrer_email + ')' : '');
                    },
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var commissionId = cell.getRow().getData().id;
                        if (commissionId) {
                            showCommissionDetails(commissionId);
                        }
                    }
                },
                {
                    title: "Referred User", 
                    field: "referred_name", 
                    formatter: function(cell) {
                        return cell.getValue() + (cell.getData().referred_email ? ' (' + cell.getData().referred_email + ')' : '');
                    },
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var commissionId = cell.getRow().getData().id;
                        if (commissionId) {
                            showCommissionDetails(commissionId);
                        }
                    }
                },
                {title: "Commission Type", field: "commission_type_display", formatter: function(cell) {
                    var type = cell.getValue();
                    var badgeClass = type === 'Signup Commission' ? 'badge-info' : 'badge-primary';
                    return '<span class="badge ' + badgeClass + '">' + type + '</span>';
                }},
                {title: "Payment ID", field: "payment_id", formatter: function(cell) {
                    var paymentId = cell.getValue();
                    return paymentId ? '<a href="/backoffice/payments/' + paymentId + '" target="_blank">#' + paymentId + '</a>' : '<span class="text-muted">Signup Bonus</span>';
                }},
                {
                    title: "Commission Amount",
                    field: "commission_amount",
                    formatter: "money",
                    formatterParams: {symbol: "$", precision: 2}
                },
                {
                    title: "Status",
                    field: "status",
                    formatter: function(cell) {
                        var status = cell.getValue();
                        var colors = {
                            'pending': 'warning',
                            'approved': 'info',
                            'paid': 'success',
                            'cancelled': 'danger'
                        };
                        return '<span class="badge badge-' + (colors[status] || 'secondary') + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                    }
                },
                {title: "Paid At", field: "paid_at", formatter: function(cell) {
                    var paidAt = cell.getValue();
                    return paidAt ? new Date(paidAt).toLocaleDateString() : 'N/A';
                }},
                {title: "Created At", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var data = cell.getRow().getData();
                        var actions = '';
                        if (data.status === 'pending' || data.status === 'approved') {
                            actions += '<div class="btn-group">';
                            actions += '<button class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Mark as Paid" onclick="updateStatus(' + data.id + ', \'paid\')"><i class="fas fa-check"></i></button>';
                            actions += '<button class="btn btn-sm btn-danger" data-toggle="tooltip" data-placement="top" title="Cancel" onclick="updateStatus(' + data.id + ', \'cancelled\')"><i class="fas fa-times"></i></button>';
                            actions += '</div>';
                        }
                        return actions;
                    }
                }
            ],
        });

        function buildUrl() {
            let url = "{{ route('affiliates.commissions') }}";
            const params = new URLSearchParams();

            const referrerId = $('#filter-referrer').val();
            if (referrerId) params.append('referrer_id', referrerId);

            const status = $('#filter-status').val();
            if (status) params.append('status', status);

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

        function updateStatus(commissionId, status) {
            var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
            window.showConfirm('Are you sure you want to mark this commission as ' + statusLabel + '?', function() {
                fetch('/backoffice/affiliates/commissions/' + commissionId + '/update-status', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: status })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw err; });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.showToast.success(data.message || 'Status updated successfully!');
                        table.replaceData();
                    } else {
                        window.showToast.error(data.message || 'Failed to update status.');
                    }
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                    window.handleAjaxError(error, 'Error updating status.');
                });
            }, 'Update Status', 'Confirm', 'Cancel');
        }

        function showCommissionDetails(commissionId) {
            if (!commissionId) {
                console.error('No commission ID provided');
                return;
            }
            
            fetch('/backoffice/affiliates/commissions/' + commissionId, {
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
                if (data.success && data.data && data.data.commission) {
                    var commission = data.data.commission;
                    $('#commissionModalReferrer').text(commission.referrer_name || 'N/A');
                    $('#commissionModalReferrerEmail').text(commission.referrer_email || 'N/A');
                    $('#commissionModalReferred').text(commission.referred_name || 'N/A');
                    $('#commissionModalReferredEmail').text(commission.referred_email || 'N/A');
                    $('#commissionModalPaymentId').html(commission.payment_id ? '<a href="/backoffice/payments/' + commission.payment_id + '" target="_blank">#' + commission.payment_id + '</a>' : '<span class="text-muted">Signup Bonus</span>');
                    $('#commissionModalType').text(commission.commission_type || 'N/A');
                    $('#commissionModalRate').text(commission.commission_rate ? commission.commission_rate + '%' : 'N/A');
                    $('#commissionModalAmount').text('$' + parseFloat(commission.commission_amount || 0).toFixed(2));
                    var statusColors = {pending: 'warning', approved: 'info', paid: 'success', cancelled: 'danger'};
                    var statusColor = statusColors[commission.status] || 'secondary';
                    $('#commissionModalStatus').html('<span class="badge badge-' + statusColor + '">' + (commission.status ? commission.status.charAt(0).toUpperCase() + commission.status.slice(1) : 'N/A') + '</span>');
                    $('#commissionModalPaidAt').text(commission.paid_at ? new Date(commission.paid_at).toLocaleDateString() : 'N/A');
                    $('#commissionModalCreated').text(commission.created_at ? new Date(commission.created_at).toLocaleDateString() : 'N/A');
                    
                    // Show modal using Bootstrap
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        var $modal = $('#commissionModal');
                        $modal.modal('show');
                        $modal.find('.close, [data-dismiss="modal"]').off('click').on('click', function() {
                            $modal.modal('hide');
                        });
                    }
                } else {
                    throw new Error('Invalid response data');
                }
            })
            .catch(error => {
                console.error('Error fetching commission details:', error);
                if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                    showToast.error('Error loading commission details: ' + (error.message || 'Unknown error'));
                } else {
                    alert('Error loading commission details: ' + (error.message || 'Unknown error'));
                }
            });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

