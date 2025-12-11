@extends('adminlte::page')

@section('title', 'Create Role')

@section('content_header')
    <h1>Create Role</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Role Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        required
                    >
                    @error('name')
                        <span class="invalid-feedback" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Permissions</label>
                    @error('permissions')
                        <div class="text-danger mb-2">{{ $message }}</div>
                    @enderror
                    
                    @if(!empty($permissions))
                        @foreach($permissions as $module => $moduleData)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <strong>{{ $moduleData['name'] }}</strong>
                                        </div>
                                        <div class="col-auto">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input module-select-all"
                                                    type="checkbox"
                                                    data-module="{{ $module }}"
                                                    id="select_all_{{ $module }}"
                                                >
                                                <label class="form-check-label" for="select_all_{{ $module }}">
                                                    Select All
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th width="25%">Read</th>
                                                <th width="25%">Write</th>
                                                <th width="25%">Update</th>
                                                <th width="25%">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    @if(isset($moduleData['permissions']['read']))
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $moduleData['permissions']['read']['permission']->name }}"
                                                                id="permission_{{ $moduleData['permissions']['read']['permission']->id }}"
                                                                data-module="{{ $module }}"
                                                                {{ in_array($moduleData['permissions']['read']['permission']->name, old('permissions', [])) ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="permission_{{ $moduleData['permissions']['read']['permission']->id }}">
                                                                {{ $moduleData['permissions']['read']['name'] }}
                                                            </label>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($moduleData['permissions']['write']))
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $moduleData['permissions']['write']['permission']->name }}"
                                                                id="permission_{{ $moduleData['permissions']['write']['permission']->id }}"
                                                                data-module="{{ $module }}"
                                                                {{ in_array($moduleData['permissions']['write']['permission']->name, old('permissions', [])) ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="permission_{{ $moduleData['permissions']['write']['permission']->id }}">
                                                                {{ $moduleData['permissions']['write']['name'] }}
                                                            </label>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($moduleData['permissions']['update']))
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $moduleData['permissions']['update']['permission']->name }}"
                                                                id="permission_{{ $moduleData['permissions']['update']['permission']->id }}"
                                                                data-module="{{ $module }}"
                                                                {{ in_array($moduleData['permissions']['update']['permission']->name, old('permissions', [])) ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="permission_{{ $moduleData['permissions']['update']['permission']->id }}">
                                                                {{ $moduleData['permissions']['update']['name'] }}
                                                            </label>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(isset($moduleData['permissions']['delete']))
                                                        <div class="form-check">
                                                            <input
                                                                class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $moduleData['permissions']['delete']['permission']->name }}"
                                                                id="permission_{{ $moduleData['permissions']['delete']['permission']->id }}"
                                                                data-module="{{ $module }}"
                                                                {{ in_array($moduleData['permissions']['delete']['permission']->name, old('permissions', [])) ? 'checked' : '' }}
                                                            >
                                                            <label class="form-check-label" for="permission_{{ $moduleData['permissions']['delete']['permission']->id }}">
                                                                {{ $moduleData['permissions']['delete']['name'] }}
                                                            </label>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No permissions available. Please run the PermissionSeeder to create permissions.</p>
                    @endif
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    @include('admin.partials.toastr')
@stop

@section('js')
    <script>
        // Select all functionality for each module
        $(document).ready(function() {
            $('.module-select-all').on('change', function() {
                const module = $(this).data('module');
                const isChecked = $(this).is(':checked');
                $(`.permission-checkbox[data-module="${module}"]`).prop('checked', isChecked);
            });

            // Update "Select All" checkbox state when individual checkboxes change
            $('.permission-checkbox').on('change', function() {
                const module = $(this).data('module');
                const moduleCheckboxes = $(`.permission-checkbox[data-module="${module}"]`);
                const allChecked = moduleCheckboxes.length === moduleCheckboxes.filter(':checked').length;
                $(`#select_all_${module}`).prop('checked', allChecked);
            });
        });
    </script>
@stop

