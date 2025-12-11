@extends('adminlte::page')

@section('title', 'Log Viewer')

@section('css')
    @include('admin.partials.toastr')
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Log: {{ $log }}</h1>
        <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <pre
                style="white-space: pre-wrap; word-wrap: break-word; background:#1e1e1e; color:#e0e0e0; padding:1rem; border-radius:4px; max-height:70vh; overflow:auto;">{{ $content }}</pre>
        </div>
    </div>
@stop

@section('js')
@stop
