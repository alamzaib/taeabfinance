@extends('adminlte::page')

@section('title', 'Create Package')

@section('content_header')
    <h1>Create Package</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('packages.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Package Name</label>
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

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="price"
                                id="price"
                                class="form-control @error('price') is-invalid @enderror"
                                value="{{ old('price') }}"
                                required
                            >
                            @error('price')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <input
                                type="text"
                                name="currency"
                                id="currency"
                                class="form-control @error('currency') is-invalid @enderror"
                                value="{{ old('currency', 'USD') }}"
                                maxlength="3"
                                required
                            >
                            @error('currency')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">3-letter code (e.g., USD, EUR)</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period">Period</label>
                            <select
                                name="period"
                                id="period"
                                class="form-control @error('period') is-invalid @enderror"
                                required
                            >
                                <option value="month" {{ old('period') == 'month' ? 'selected' : '' }}>Monthly</option>
                                <option value="year" {{ old('period') == 'year' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            @error('period')
                                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="features">Features</label>
                    <div id="features-container">
                        @if(old('features'))
                            @foreach(old('features') as $index => $feature)
                                <div class="input-group mb-2">
                                    <input
                                        type="text"
                                        name="features[]"
                                        class="form-control"
                                        value="{{ $feature }}"
                                        placeholder="Enter a feature"
                                    >
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-danger remove-feature" type="button">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="add-feature">
                        <i class="fas fa-plus"></i> Add Feature
                    </button>
                    @error('features')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="hidden" name="popular" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="popular"
                                    id="popular"
                                    value="1"
                                    {{ old('popular') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="popular">
                                    Mark as Popular
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="hidden" name="active" value="0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="active"
                                    id="active"
                                    value="1"
                                    {{ old('active', true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('packages.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Add feature input
            $('#add-feature').on('click', function() {
                const featureInput = `
                    <div class="input-group mb-2">
                        <input
                            type="text"
                            name="features[]"
                            class="form-control"
                            placeholder="Enter a feature"
                        >
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger remove-feature" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
                $('#features-container').append(featureInput);
            });

            // Remove feature input
            $(document).on('click', '.remove-feature', function() {
                $(this).closest('.input-group').remove();
            });
        });
    </script>
@stop

