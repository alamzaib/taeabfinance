@extends('adminlte::page')

@section('title', 'Analytics')

@section('content_header')
    <h1>Analytics Dashboard</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue by Month</h3>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">User Growth</h3>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Status Distribution</h3>
                </div>
                <div class="card-body">
                    <canvas id="paymentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Revenue Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenueByMonth->pluck('month')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($revenueByMonth->pluck('total')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            }
        });

        // User Growth Chart
        var userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($userGrowth->pluck('month')) !!},
                datasets: [{
                    label: 'New Users',
                    data: {!! json_encode($userGrowth->pluck('count')) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            }
        });

        // Payment Status Chart
        var paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
        new Chart(paymentStatusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($paymentStatus->pluck('status')) !!},
                datasets: [{
                    data: {!! json_encode($paymentStatus->pluck('count')) !!},
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8']
                }]
            }
        });
    </script>
@stop

