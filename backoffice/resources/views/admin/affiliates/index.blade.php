@extends('adminlte::page')

@section('title', 'Affiliate Links Management')

@section('content_header')
    <div class="row">
        <div class="col-md-4">
            <h1>Affiliate Links</h1>
        </div>
        <div class="col-md-8 text-right">
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <div id="affiliate-links-table" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Affiliate Link Details Modal -->
    <div class="modal fade" id="affiliateLinkModal" tabindex="-1" role="dialog" aria-labelledby="affiliateLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="affiliateLinkModalLabel">Affiliate Link Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>User:</strong> <span id="linkModalUser">-</span></p>
                            <p><strong>Email:</strong> <span id="linkModalEmail">-</span></p>
                            <p><strong>Affiliate Code:</strong> <span id="linkModalCode">-</span></p>
                            <p><strong>Status:</strong> <span id="linkModalStatus">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Clicks:</strong> <span id="linkModalClicks">-</span></p>
                            <p><strong>Signups:</strong> <span id="linkModalSignups">-</span></p>
                            <p><strong>Created At:</strong> <span id="linkModalCreated">-</span></p>
                            <p><strong>Updated At:</strong> <span id="linkModalUpdated">-</span></p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Affiliate Link:</strong>
                            <p id="linkModalLink" class="mt-2"></p>
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

        var table = new Tabulator("#affiliate-links-table", {
            ajaxURL: "{{ route('affiliates.index') }}",
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
                if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                    return;
                }
                var linkId = row.getData().id;
                if (linkId) {
                    showAffiliateLinkDetails(linkId);
                }
            },
            columns: [
                {
                    title: "ID", 
                    field: "id", 
                    width: 80,
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var linkId = cell.getRow().getData().id;
                        if (linkId) {
                            showAffiliateLinkDetails(linkId);
                        }
                    }
                },
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
                        var linkId = cell.getRow().getData().id;
                        if (linkId) {
                            showAffiliateLinkDetails(linkId);
                        }
                    }
                },
                {
                    title: "Affiliate Code", 
                    field: "affiliate_code",
                    cellDblClick: function(e, cell) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('.btn')) {
                            return;
                        }
                        var linkId = cell.getRow().getData().id;
                        if (linkId) {
                            showAffiliateLinkDetails(linkId);
                        }
                    }
                },
                {
                    title: "Public Affiliate Link",
                    field: "affiliate_link",
                    formatter: "link",
                    formatterParams: {
                        target: "_blank",
                        label: function(cell) {
                            return cell.getValue();
                        }
                    },
                    width: 400
                },
                {title: "Clicks", field: "clicks", formatter: "number", width: 100},
                {title: "Signups", field: "signups", formatter: "number", width: 100},
                {
                    title: "Status",
                    field: "active",
                    formatter: function(cell) {
                        var active = cell.getValue();
                        return '<span class="badge badge-' + (active ? 'success' : 'secondary') + '">' + (active ? 'Active' : 'Inactive') + '</span>';
                    },
                    width: 120
                },
                {title: "Created At", field: "created_at", formatter: "datetime", formatterParams: {inputFormat: "YYYY-MM-DD HH:mm:ss", outputFormat: "MM/DD/YYYY"}, width: 150},
                {
                    title: "Actions",
                    formatter: "html",
                    formatter: function(cell) {
                        var data = cell.getRow().getData();
                        var actions = '<button class="btn btn-sm btn-' + (data.active ? 'warning' : 'success') + '" data-toggle="tooltip" data-placement="top" title="' + (data.active ? 'Deactivate' : 'Activate') + '" onclick="toggleStatus(' + data.id + ')"><i class="fas fa-' + (data.active ? 'ban' : 'check') + '"></i></button>';
                        return actions;
                    },
                    width: 100
                }
            ],
        });

        function toggleStatus(linkId) {
            window.showConfirm('Are you sure you want to toggle the status of this affiliate link?', function() {
                fetch('/backoffice/affiliates/links/' + linkId + '/toggle-status', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
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
            }, 'Toggle Status', 'Confirm', 'Cancel');
        }

        function showAffiliateLinkDetails(linkId) {
            if (!linkId) {
                console.error('No affiliate link ID provided');
                return;
            }
            
            fetch('/backoffice/affiliates/links/' + linkId, {
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
                if (data.success && data.data && data.data.affiliateLink) {
                    var link = data.data.affiliateLink;
                    $('#linkModalUser').text(link.user_name || 'N/A');
                    $('#linkModalEmail').text(link.user_email || 'N/A');
                    $('#linkModalCode').text(link.affiliate_code || 'N/A');
                    $('#linkModalClicks').text(link.clicks || 0);
                    $('#linkModalSignups').text(link.signups || 0);
                    $('#linkModalLink').html('<a href="' + (link.affiliate_link || '#') + '" target="_blank">' + (link.affiliate_link || 'N/A') + '</a>');
                    $('#linkModalStatus').html('<span class="badge badge-' + (link.active ? 'success' : 'secondary') + '">' + (link.active ? 'Active' : 'Inactive') + '</span>');
                    $('#linkModalCreated').text(link.created_at ? new Date(link.created_at).toLocaleDateString() : 'N/A');
                    $('#linkModalUpdated').text(link.updated_at ? new Date(link.updated_at).toLocaleDateString() : 'N/A');
                    
                    // Show modal using Bootstrap
                    if (typeof jQuery !== 'undefined' && jQuery.fn.modal) {
                        var $modal = $('#affiliateLinkModal');
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
                console.error('Error fetching affiliate link details:', error);
                if (typeof showToast !== 'undefined' && showToast && showToast.error) {
                    showToast.error('Error loading affiliate link details: ' + (error.message || 'Unknown error'));
                } else {
                    alert('Error loading affiliate link details: ' + (error.message || 'Unknown error'));
                }
            });
        }

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

