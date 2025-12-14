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
            columns: [
                {title: "ID", field: "id", width: 80},
                {title: "User", field: "user_name", formatter: function(cell) {
                    return cell.getValue() + (cell.getData().user_email ? ' (' + cell.getData().user_email + ')' : '');
                }},
                {title: "Affiliate Code", field: "affiliate_code"},
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

        // Initialize tooltips on table data loaded
        table.on("dataLoaded", function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@stop

