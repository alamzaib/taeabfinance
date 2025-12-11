@extends('adminlte::page')

@section('title', 'Support Tickets')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Support Tickets</h1>
        </div>
        <div class="col-md-8 text-right">
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="tickets-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Support Ticket Details Modal -->
    <div class="modal fade" id="ticketModal" tabindex="-1" role="dialog" aria-labelledby="ticketModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ticketModalLabel">Support Ticket Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Ticket #:</strong> <span id="ticketModalNumber"></span></p>
                            <p><strong>User:</strong> <span id="ticketModalUser"></span></p>
                            <p><strong>Subject:</strong> <span id="ticketModalSubject"></span></p>
                            <p><strong>Priority:</strong> <span id="ticketModalPriority"></span></p>
                            <p><strong>Status:</strong> <span id="ticketModalStatus"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Assigned To:</strong> <span id="ticketModalAssigned"></span></p>
                            <p><strong>Resolved At:</strong> <span id="ticketModalResolved"></span></p>
                            <p><strong>Created At:</strong> <span id="ticketModalCreated"></span></p>
                            <p><strong>Updated At:</strong> <span id="ticketModalUpdated"></span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Message:</strong>
                            <div class="border p-3 mt-2" id="ticketModalMessage"></div>
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
        var table = new Tabulator("#tickets-table", {
            ajaxURL: "{{ route('support-tickets.index') }}",
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
                var ticketId = row.getData().id;
                showTicketDetails(ticketId);
            },
            columns: [
                {
                    title: "Ticket #",
                    field: "ticket_number",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var ticketId = cell.getRow().getData().id;
                        showTicketDetails(ticketId);
                    }
                },
                {title: "User", field: "user_name"},
                {
                    title: "Subject",
                    field: "subject",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        var ticketId = cell.getRow().getData().id;
                        showTicketDetails(ticketId);
                    }
                },
                {title: "Priority", field: "priority", formatter: function(cell) {
                    var priority = cell.getValue();
                    var colors = {low: 'info', medium: 'warning', high: 'danger', urgent: 'dark'};
                    return '<span class="badge badge-' + (colors[priority] || 'secondary') + '">' + priority.charAt(0).toUpperCase() + priority.slice(1) + '</span>';
                }},
                {title: "Status", field: "status", formatter: function(cell) {
                    var status = cell.getValue();
                    var colors = {open: 'success', in_progress: 'warning', resolved: 'info', closed: 'secondary'};
                    return '<span class="badge badge-' + (colors[status] || 'secondary') + '">' + status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</span>';
                }},
                {title: "Assigned To", field: "assigned_to_name"},
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

        function showTicketDetails(ticketId) {
            fetch('/backoffice/support-tickets/' + ticketId, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var ticket = data.data.supportTicket;
                        $('#ticketModalNumber').text(ticket.ticket_number);
                        $('#ticketModalUser').text(ticket.user ? ticket.user.name + ' (' + ticket.user.email + ')' : 'N/A');
                        $('#ticketModalSubject').text(ticket.subject);
                        $('#ticketModalMessage').text(ticket.message || 'N/A');
                        
                        var priorityColors = {low: 'info', medium: 'warning', high: 'danger', urgent: 'dark'};
                        var priorityColor = priorityColors[ticket.priority] || 'secondary';
                        $('#ticketModalPriority').html('<span class="badge badge-' + priorityColor + '">' + ticket.priority.charAt(0).toUpperCase() + ticket.priority.slice(1) + '</span>');
                        
                        var statusColors = {open: 'success', in_progress: 'warning', resolved: 'info', closed: 'secondary'};
                        var statusColor = statusColors[ticket.status] || 'secondary';
                        var statusText = ticket.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        $('#ticketModalStatus').html('<span class="badge badge-' + statusColor + '">' + statusText + '</span>');
                        
                        $('#ticketModalAssigned').text(ticket.assigned_to ? ticket.assigned_to.name : 'Unassigned');
                        $('#ticketModalResolved').text(ticket.resolved_at ? new Date(ticket.resolved_at).toLocaleDateString() : 'N/A');
                        $('#ticketModalCreated').text(new Date(ticket.created_at).toLocaleDateString());
                        $('#ticketModalUpdated').text(new Date(ticket.updated_at).toLocaleDateString());
                        
                        $('#ticketModal').modal('show');
                    }
                })
                .catch(error => {
                    console.error('Error fetching ticket details:', error);
                    showToast.error('Error loading ticket details');
                });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

