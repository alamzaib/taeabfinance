@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-1"><strong>Created:</strong> {{ $user->created_at?->format('Y-m-d H:i') }}</p>
                    <p class="mb-3"><strong>Updated:</strong> {{ $user->updated_at?->format('Y-m-d H:i') }}</p>
                    <p class="mb-1"><strong>Roles:</strong></p>
                    <div>
                        @forelse($user->roles as $role)
                            <span class="badge badge-info mr-1">{{ $role->name }}</span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
                <div>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop


