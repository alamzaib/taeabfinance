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
            columns: [
                {title: "ID", field: "id", width: 80},
                {title: "Referrer", field: "referrer_name", formatter: function(cell) {
                    return cell.getValue() + (cell.getData().referrer_email ? ' (' + cell.getData().referrer_email + ')' : '');
                }},
                {title: "Referred User", field: "referred_name", formatter: function(cell) {
                    return cell.getValue() + (cell.getData().referred_email ? ' (' + cell.getData().referred_email + ')' : '');
                }},
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

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

