{{-- Toastr CSS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

{{-- Load toastr and SweetAlert2 in JS section to ensure proper loading order after jQuery --}}
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Wait for jQuery and toastr to be fully loaded
    (function() {
        function initToastr() {
            // Check if jQuery is available (toastr depends on it)
            if (typeof jQuery === 'undefined' || !jQuery) {
                setTimeout(initToastr, 50);
                return;
            }
            
            // Check if toastr is available
            if (typeof toastr === 'undefined' || !toastr) {
                setTimeout(initToastr, 50);
                return;
            }

            // Configure Toastr
            try {
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
            } catch (e) {
                console.error('Error configuring toastr:', e);
            }

            // Helper functions for toast notifications with robust error handling
            window.showToast = {
                success: function(message, title) {
                    title = title || 'Success';
                    try {
                        // Check if toastr is fully initialized
                        if (typeof toastr !== 'undefined' && toastr && 
                            typeof toastr.success === 'function' && 
                            typeof toastr.options !== 'undefined') {
                            toastr.success(message, title);
                        } else {
                            console.warn('Toastr not ready, using alert');
                            alert(title + ': ' + message);
                        }
                    } catch (e) {
                        console.error('Error showing toast:', e);
                        alert(title + ': ' + message);
                    }
                },
                error: function(message, title) {
                    title = title || 'Error';
                    try {
                        // Check if toastr is fully initialized
                        if (typeof toastr !== 'undefined' && toastr && 
                            typeof toastr.error === 'function' && 
                            typeof toastr.options !== 'undefined') {
                            toastr.error(message, title);
                        } else {
                            console.warn('Toastr not ready, using alert');
                            alert(title + ': ' + message);
                        }
                    } catch (e) {
                        console.error('Error showing toast:', e);
                        alert(title + ': ' + message);
                    }
                },
                warning: function(message, title) {
                    title = title || 'Warning';
                    try {
                        // Check if toastr is fully initialized
                        if (typeof toastr !== 'undefined' && toastr && 
                            typeof toastr.warning === 'function' && 
                            typeof toastr.options !== 'undefined') {
                            toastr.warning(message, title);
                        } else {
                            console.warn('Toastr not ready, using alert');
                            alert(title + ': ' + message);
                        }
                    } catch (e) {
                        console.error('Error showing toast:', e);
                        alert(title + ': ' + message);
                    }
                },
                info: function(message, title) {
                    title = title || 'Info';
                    try {
                        // Check if toastr is fully initialized
                        if (typeof toastr !== 'undefined' && toastr && 
                            typeof toastr.info === 'function' && 
                            typeof toastr.options !== 'undefined') {
                            toastr.info(message, title);
                        } else {
                            console.info('Toastr not ready, using alert');
                            alert(title + ': ' + message);
                        }
                    } catch (e) {
                        console.error('Error showing toast:', e);
                        alert(title + ': ' + message);
                    }
                }
            };

            // SweetAlert2 for better confirmations
            window.showConfirm = function(message, callback, title, confirmText, cancelText) {
                title = title || 'Are you sure?';
                confirmText = confirmText || 'Yes';
                cancelText = cancelText || 'Cancel';
                
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
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
                } else {
                    if (confirm(title + ': ' + message)) {
                        callback();
                    }
                }
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
            window.handleAjaxError = function(error, defaultMessage) {
                defaultMessage = defaultMessage || 'An error occurred';
                
                if (error && error.response && error.response.data) {
                    if (error.response.data.errors) {
                        handleValidationErrors(error.response.data.errors);
                    } else if (error.response.data.message) {
                        showToast.error(error.response.data.message);
                    } else {
                        showToast.error(defaultMessage);
                    }
                } else if (error && error.message) {
                    showToast.error(error.message);
                } else {
                    showToast.error(defaultMessage);
                }
            };

            // Display Laravel flash messages as toasts (only if they exist)
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
            @if(isset($errors) && $errors->any())
                @foreach($errors->all() as $error)
                    showToast.error('{{ $error }}', 'Validation Error');
                @endforeach
            @endif
        }

        // Initialize when DOM is ready and jQuery is fully loaded
        function waitForDependencies() {
            // Check if jQuery is loaded and ready
            if (typeof jQuery === 'undefined' || !jQuery || typeof jQuery.fn === 'undefined') {
                setTimeout(waitForDependencies, 100);
                return;
            }
            
            // Use jQuery ready to ensure everything is loaded
            jQuery(document).ready(function() {
                // Additional small delay to ensure toastr script is fully parsed
                setTimeout(initToastr, 100);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', waitForDependencies);
        } else {
            waitForDependencies();
        }
    })();
</script>
@endpush
