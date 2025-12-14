@extends('adminlte::page')

@section('title', 'Payments Management')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Payments Management</h1>
        </div>
        <div class="col-md-8 text-right">
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="payments-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Payment Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Transaction ID:</strong> <span id="paymentModalTransactionId"></span></p>
                            <p><strong>User:</strong> <span id="paymentModalUser"></span></p>
                            <p><strong>Package:</strong> <span id="paymentModalPackage"></span></p>
                            <p><strong>Amount:</strong> <span id="paymentModalAmount"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="paymentModalStatus"></span></p>
                            <p><strong>Payment Method:</strong> <span id="paymentModalMethod"></span></p>
                            <p><strong>Refund Requests:</strong> <span id="paymentModalRefunds"></span></p>
                            <p><strong>Created At:</strong> <span id="paymentModalCreated"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3" id="paymentLinkSection" style="display: none;">
                        <div class="col-12">
                            <hr>
                            <p><strong>Payment Link:</strong></p>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="paymentModalLink" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyPaymentLink()" data-toggle="tooltip" title="Copy Link">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <a href="#" id="paymentModalLinkAnchor" target="_blank" class="btn btn-sm btn-primary">
                                <i class="fas fa-external-link-alt"></i> Open Payment Link
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3" id="generateLinkSection">
                        <div class="col-12">
                            <hr>
                            <button type="button" class="btn btn-success" id="generateLinkBtn" onclick="generatePaymentLink()">
                                <i class="fas fa-link"></i> Generate Payment Link
                            </button>
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
        var table = new Tabulator("#payments-table", {
            ajaxURL: "{{ route('payments.index') }}",
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
                var paymentId = row.getData().id;
                showPaymentDetails(paymentId);
            },
            columns: [
                {title: "ID", field: "id", width: 80},
                {
                    title: "Transaction ID",
                    field: "transaction_id",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var paymentId = cell.getRow().getData().id;
                        showPaymentDetails(paymentId);
                    }
                },
                {title: "User", field: "user_name"},
                {title: "Package", field: "package_name"},
                {title: "Amount", field: "amount", formatter: "money", formatterParams: {symbol: "$", precision: 2}},
                {title: "Status", field: "status", formatter: function(cell) {
                    var status = cell.getValue();
                    var colors = {completed: 'success', pending: 'warning', failed: 'danger', refunded: 'info'};
                    return '<span class="badge badge-' + (colors[status] || 'secondary') + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                }},
                {title: "Date", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}},
                {
                    title: "Payment Link",
                    field: "has_payment_link",
                    formatter: function(cell) {
                        var row = cell.getRow();
                        var data = row.getData();
                        if (data.has_payment_link) {
                            return '<span class="badge badge-success"><i class="fas fa-check"></i> Generated</span>';
                        } else if (data.status === 'pending') {
                            return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>';
                        }
                        return '<span class="badge badge-secondary">N/A</span>';
                    }
                },
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var row = cell.getRow();
                        var data = row.getData();
                        var actions = '';
                        
                        if (data.status === 'pending' && !data.has_payment_link) {
                            actions += '<button class="btn btn-sm btn-success" data-toggle="tooltip" data-placement="top" title="Generate Payment Link" onclick="event.stopPropagation(); generatePaymentLinkFromTable(' + data.id + ');"><i class="fas fa-link"></i></button> ';
                        }
                        
                        if (data.has_payment_link) {
                            actions += '<button class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="View Payment Link" onclick="event.stopPropagation(); viewPaymentLink(' + data.id + ');"><i class="fas fa-eye"></i></button> ';
                        }
                        
                        return actions;
                    }
                }
            ],
        });

        var currentPaymentId = null;

        function showPaymentDetails(paymentId) {
            currentPaymentId = paymentId;
            fetch('/backoffice/payments/' + paymentId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var payment = data.data.payment;
                        $('#paymentModalTransactionId').text(payment.transaction_id);
                        $('#paymentModalUser').text(payment.user ? payment.user.name + ' (' + payment.user.email + ')' : 'N/A');
                        $('#paymentModalPackage').text(payment.package ? payment.package.name : 'N/A');
                        $('#paymentModalAmount').text(payment.currency + ' ' + parseFloat(payment.amount).toFixed(2));
                        
                        var statusColors = {completed: 'success', pending: 'warning', failed: 'danger', refunded: 'info'};
                        var statusColor = statusColors[payment.status] || 'secondary';
                        $('#paymentModalStatus').html('<span class="badge badge-' + statusColor + '">' + payment.status.charAt(0).toUpperCase() + payment.status.slice(1) + '</span>');
                        
                        $('#paymentModalMethod').text(payment.payment_method || 'N/A');
                        $('#paymentModalRefunds').text(payment.refund_requests_count || 0);
                        $('#paymentModalCreated').text(new Date(payment.created_at).toLocaleDateString());
                        
                        // Show/hide payment link section
                        if (payment.payment_link) {
                            $('#paymentModalLink').val(payment.payment_link);
                            $('#paymentModalLinkAnchor').attr('href', payment.payment_link);
                            $('#paymentLinkSection').show();
                            $('#generateLinkSection').hide();
                        } else {
                            $('#paymentLinkSection').hide();
                            if (payment.status === 'pending') {
                                $('#generateLinkSection').show();
                            } else {
                                $('#generateLinkSection').hide();
                            }
                        }
                        
                        $('#paymentModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching payment details:', error);
                    window.showToast.error('Error loading payment details');
                });
        }

        function generatePaymentLink() {
            if (!currentPaymentId) return;
            
            window.showConfirm('Generate a Stripe payment link for this payment?', function() {
                fetch('/backoffice/payments/' + currentPaymentId + '/generate-payment-link', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.showToast.success(data.message || 'Payment link generated successfully');
                        $('#paymentModalLink').val(data.data.payment_link);
                        $('#paymentModalLinkAnchor').attr('href', data.data.payment_link);
                        $('#paymentLinkSection').show();
                        $('#generateLinkSection').hide();
                        table.replaceData();
                    } else {
                        window.showToast.error(data.message || 'Error generating payment link');
                    }
                })
                .catch(error => {
                    console.error('Error generating payment link:', error);
                    window.handleAjaxError(error, 'Error generating payment link');
                });
            }, 'Generate Payment Link', 'Generate', 'Cancel');
        }

        function generatePaymentLinkFromTable(paymentId) {
            currentPaymentId = paymentId;
            generatePaymentLink();
        }

        function viewPaymentLink(paymentId) {
            showPaymentDetails(paymentId);
        }

        function copyPaymentLink() {
            var linkInput = document.getElementById('paymentModalLink');
            linkInput.select();
            linkInput.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand('copy');
            window.showToast.success('Payment link copied to clipboard!');
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

