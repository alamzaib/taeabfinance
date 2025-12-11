@extends('adminlte::page')

@section('title', 'System Logs')

@section('content_header')
    <h1>System Logs</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Log Files</h3>
        </div>
        <div class="card-body">
            <div class="list-group">
                @php
                    $logFiles = \Illuminate\Support\Facades\File::files(storage_path('logs'));
                @endphp
                @foreach($logFiles as $logFile)
                    <a href="{{ route('logs.show', basename($logFile)) }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt mr-2"></i> {{ basename($logFile) }}
                        <span class="float-right text-muted">{{ \Carbon\Carbon::createFromTimestamp(filemtime($logFile))->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@stop

