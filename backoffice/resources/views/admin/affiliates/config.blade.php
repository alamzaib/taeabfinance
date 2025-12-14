@extends('adminlte::page')

@section('title', 'Affiliate Configuration')

@section('content_header')
    <h1>Affiliate Configuration</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Commission Settings</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Note:</strong> 
                <ul class="mb-0 pl-3">
                    <li><strong>Signup Commissions:</strong> Automatically generated when a user registers with an affiliate link (uses Signup Bonus Amount).</li>
                    <li><strong>Payment Commissions:</strong> Automatically generated when a referred user completes a payment (uses commission type and rate/amount above).</li>
                </ul>
            </div>
            <form id="config-form">
                <div class="form-group">
                    <label for="commission_type">Commission Type</label>
                    <select class="form-control" id="commission_type" name="commission_type" required>
                        <option value="percentage" {{ ($configs['commission_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ ($configs['commission_type'] ?? 'percentage') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                    </select>
                    <small class="form-text text-muted">Choose whether commissions are calculated as a percentage or fixed amount.</small>
                </div>

                <div class="form-group" id="commission-rate-group">
                    <label for="commission_rate">Commission Rate (%)</label>
                    <input type="number" class="form-control" id="commission_rate" name="commission_rate" 
                           value="{{ $configs['commission_rate'] ?? '10' }}" min="0" max="100" step="0.01" required>
                    <small class="form-text text-muted">Percentage of payment amount to be given as commission (0-100).</small>
                </div>

                <div class="form-group" id="fixed-amount-group" style="display: none;">
                    <label for="fixed_commission_amount">Fixed Commission Amount ($)</label>
                    <input type="number" class="form-control" id="fixed_commission_amount" name="fixed_commission_amount" 
                           value="{{ $configs['fixed_commission_amount'] ?? '0' }}" min="0" step="0.01">
                    <small class="form-text text-muted">Fixed amount to be given as commission for each payment.</small>
                </div>

                <div class="form-group">
                    <label for="signup_bonus_amount">Signup Bonus Amount ($)</label>
                    <input type="number" class="form-control" id="signup_bonus_amount" name="signup_bonus_amount" 
                           value="{{ $configs['signup_bonus_amount'] ?? '0' }}" min="0" step="0.01">
                    <small class="form-text text-muted">Fixed commission amount given when a referred user completes registration. Set to 0 to disable signup commissions.</small>
                </div>

                <div class="form-group">
                    <label for="min_payment_for_commission">Minimum Payment for Commission ($)</label>
                    <input type="number" class="form-control" id="min_payment_for_commission" name="min_payment_for_commission" 
                           value="{{ $configs['min_payment_for_commission'] ?? '0' }}" min="0" step="0.01">
                    <small class="form-text text-muted">Minimum payment amount required to generate a commission. Set to 0 to allow all payments.</small>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </form>
        </div>
    </div>
@stop

@section('css')
    @include('admin.partials.toastr')
@stop

@section('js')
    @stack('scripts')
    <script>
        const csrfToken = '{{ csrf_token() }}';

        // Show/hide fields based on commission type
        $('#commission_type').on('change', function() {
            if ($(this).val() === 'percentage') {
                $('#commission-rate-group').show();
                $('#fixed-amount-group').hide();
                $('#commission_rate').prop('required', true);
                $('#fixed_commission_amount').prop('required', false);
            } else {
                $('#commission-rate-group').hide();
                $('#fixed-amount-group').show();
                $('#commission_rate').prop('required', false);
                $('#fixed_commission_amount').prop('required', true);
            }
        });

        // Trigger on page load
        $('#commission_type').trigger('change');

        $('#config-form').on('submit', function(e) {
            e.preventDefault();

            var formData = {
                commission_type: $('#commission_type').val(),
                signup_bonus_amount: $('#signup_bonus_amount').val(),
                min_payment_for_commission: $('#min_payment_for_commission').val()
            };

            if ($('#commission_type').val() === 'percentage') {
                formData.commission_rate = $('#commission_rate').val();
            } else if ($('#commission_type').val() === 'fixed') {
                formData.fixed_commission_amount = $('#fixed_commission_amount').val();
            }

            fetch('{{ route('affiliates.config.update') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw err; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.showToast.success(data.message || 'Configuration saved successfully!');
                } else {
                    window.showToast.error(data.message || 'Failed to save configuration.');
                }
            })
            .catch(error => {
                console.error('Error saving configuration:', error);
                window.handleAjaxError(error, 'Error saving configuration.');
            });
        });
    </script>
@stop

