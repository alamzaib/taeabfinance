{{-- Toastr CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

{{-- Toastr JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
{{-- SweetAlert2 for confirmations --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Helper functions for toast notifications
    window.showToast = {
        success: function(message, title = 'Success') {
            toastr.success(message, title);
        },
        error: function(message, title = 'Error') {
            toastr.error(message, title);
        },
        warning: function(message, title = 'Warning') {
            toastr.warning(message, title);
        },
        info: function(message, title = 'Info') {
            toastr.info(message, title);
        }
    };

    // SweetAlert2 for better confirmations
    window.showConfirm = function(message, callback, title = 'Are you sure?', confirmText = 'Yes', cancelText = 'Cancel') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        }).then((result) => {
            if (result.isConfirmed) {
                callback();
            }
        });
    };

    // Handle Laravel validation errors from AJAX responses
    window.handleValidationErrors = function(errors) {
        if (typeof errors === 'object') {
            let errorMessages = [];
            if (errors.errors) {
                // Laravel validation error format
                Object.keys(errors.errors).forEach(function(key) {
                    errors.errors[key].forEach(function(message) {
                        errorMessages.push(message);
                    });
                });
            } else {
                // Plain object with error messages
                Object.keys(errors).forEach(function(key) {
                    if (Array.isArray(errors[key])) {
                        errors[key].forEach(function(message) {
                            errorMessages.push(message);
                        });
                    } else {
                        errorMessages.push(errors[key]);
                    }
                });
            }
            errorMessages.forEach(function(message) {
                showToast.error(message);
            });
        } else if (typeof errors === 'string') {
            showToast.error(errors);
        }
    };

    // Handle AJAX error responses
    window.handleAjaxError = function(error, defaultMessage = 'An error occurred') {
        if (error.response && error.response.data) {
            if (error.response.data.errors) {
                handleValidationErrors(error.response.data.errors);
            } else if (error.response.data.message) {
                showToast.error(error.response.data.message);
            } else {
                showToast.error(defaultMessage);
            }
        } else if (error.message) {
            showToast.error(error.message);
        } else {
            showToast.error(defaultMessage);
        }
    };

    // Display Laravel flash messages as toasts
    @if(session('success'))
        showToast.success('{{ session('success') }}', 'Success');
    @endif

    @if(session('error'))
        showToast.error('{{ session('error') }}', 'Error');
    @endif

    @if(session('warning'))
        showToast.warning('{{ session('warning') }}', 'Warning');
    @endif

    @if(session('info'))
        showToast.info('{{ session('info') }}', 'Info');
    @endif

    // Display validation errors from session
    @if($errors->any())
        @foreach($errors->all() as $error)
            showToast.error('{{ $error }}', 'Validation Error');
        @endforeach
    @endif
</script>

