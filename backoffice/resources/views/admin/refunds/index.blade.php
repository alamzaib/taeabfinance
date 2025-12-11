@extends('adminlte::page')

@section('title', 'Refund Requests')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Refund Requests</h1>
        </div>
        <div class="col-md-8 text-right">
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="refunds-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Refund Request Details Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">Refund Request Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User:</strong> <span id="refundModalUser"></span></p>
                            <p><strong>Payment ID:</strong> <span id="refundModalPaymentId"></span></p>
                            <p><strong>Transaction ID:</strong> <span id="refundModalTransactionId"></span></p>
                            <p><strong>Amount:</strong> <span id="refundModalAmount"></span></p>
                            <p><strong>Reason:</strong> <span id="refundModalReason"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="refundModalStatus"></span></p>
                            <p><strong>Description:</strong> <span id="refundModalDescription"></span></p>
                            <p><strong>Admin Notes:</strong> <span id="refundModalAdminNotes"></span></p>
                            <p><strong>Processed By:</strong> <span id="refundModalProcessor"></span></p>
                            <p><strong>Processed At:</strong> <span id="refundModalProcessedAt"></span></p>
                            <p><strong>Created At:</strong> <span id="refundModalCreated"></span></p>
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
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.4.4/build/global/luxon.min.js"></script>
    <script type="text/javascript" src="https://unpkg.com/tabulator-tables@5.5.2/dist/js/tabulator.min.js"></script>
    <script>
        var table = new Tabulator("#refunds-table", {
            ajaxURL: "{{ route('refunds.index') }}",
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
                var refundId = row.getData().id;
                showRefundDetails(refundId);
            },
            columns: [
                {title: "ID", field: "id", width: 80},
                {title: "User", field: "user_name"},
                {
                    title: "Payment ID",
                    field: "payment_id",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var refundId = cell.getRow().getData().id;
                        showRefundDetails(refundId);
                    }
                },
                {
                    title: "Reason",
                    field: "reason",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var refundId = cell.getRow().getData().id;
                        showRefundDetails(refundId);
                    }
                },
                {title: "Status", field: "status", formatter: function(cell) {
                    var status = cell.getValue();
                    var colors = {pending: 'warning', approved: 'success', rejected: 'danger', processed: 'info'};
                    return '<span class="badge badge-' + (colors[status] || 'secondary') + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                }},
                {title: "Date", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        return '';
                    }
                }
            ],
        });

        function showRefundDetails(refundId) {
            fetch('/backoffice/refunds/' + refundId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var refund = data.data.refundRequest;
                        $('#refundModalUser').text(refund.user ? refund.user.name + ' (' + refund.user.email + ')' : 'N/A');
                        $('#refundModalPaymentId').text(refund.payment ? refund.payment.id : 'N/A');
                        $('#refundModalTransactionId').text(refund.payment ? refund.payment.transaction_id : 'N/A');
                        $('#refundModalAmount').text(refund.payment ? '$' + parseFloat(refund.payment.amount).toFixed(2) : 'N/A');
                        $('#refundModalReason').text(refund.reason || 'N/A');
                        $('#refundModalDescription').text(refund.description || 'N/A');
                        $('#refundModalAdminNotes').text(refund.admin_notes || 'N/A');
                        $('#refundModalProcessor').text(refund.processor ? refund.processor.name : 'N/A');
                        $('#refundModalProcessedAt').text(refund.processed_at ? new Date(refund.processed_at).toLocaleDateString() : 'N/A');
                        $('#refundModalCreated').text(new Date(refund.created_at).toLocaleDateString());
                        
                        var statusColors = {pending: 'warning', approved: 'success', rejected: 'danger', processed: 'info'};
                        var statusColor = statusColors[refund.status] || 'secondary';
                        $('#refundModalStatus').html('<span class="badge badge-' + statusColor + '">' + refund.status.charAt(0).toUpperCase() + refund.status.slice(1) + '</span>');
                        
                        $('#refundModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching refund details:', error);
                    showToast.error('Error loading refund details');
                });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

