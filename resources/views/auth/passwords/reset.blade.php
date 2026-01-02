@extends('layouts/basic')


{{-- Page content --}}
@section('content')



<style>
    .login-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        z-index: 1000;
        padding: 12px;
        box-sizing: border-box;
    }
    @media (max-width: 767.98px) {
        .login-container {
            position: static;
            transform: none;
            padding-top: 24px;
            padding-bottom: 24px;
        }
    }
    .box.login-box {
        border-radius: 32px;
        overflow: hidden;
        background: #ffffff !important;
    }
    .box-header .box-title {
        width: 100%;
        text-align: center;
    }
    /* Add padding to form fields and labels */
    .login-box-body {
        padding-left: 40px;
        padding-right: 40px;
    }
    .login-box-body label {
        padding-left: 4px;
        padding-right: 4px;
    }
    .login-box-body .form-control {
        padding-left: 12px;
        padding-right: 12px;
    }
</style>

<div class="login-bg" aria-hidden="true" style="position:fixed;inset:0;z-index:800;background-image:url('{{ asset('custom/img/loginbg.jpg') }}');background-size:cover;background-position:center center;background-repeat:no-repeat;pointer-events:none;"></div>

<form class="form-horizontal" role="form" method="POST" action="{{ url('/password/reset') }}">
    {!! csrf_field() !!}
    <div class="container login-container">
        <div class="row">
            <div class="col-md-4 ">
                <div class="box login-box">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ trans('auth/general.reset_password')  }}</h2>
                    </div>



                    <div class="login-box-body">
                        <div class="row">
                            <!-- Notifications -->
                            @include('notifications')
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                <label for="username">
                                    <x-icon type="user" /> {{ trans('admin/users/table.username')  }}
                                </label>
                                <input type="text" class="form-control" name="username" value="{{ old('username', $username) }}">
                                {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                            </div>
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" style="position:relative;">
                                <label for="password">
                                    <x-icon type="password" />
                                    {{ trans('admin/users/table.password')  }}
                                </label>
                                <div style="position:relative;">
                                    <input type="password" class="form-control pr-5" name="password" id="password" aria-label="password" style="padding-right:2.5rem;">
                                    <span class="toggle-password" id="togglePassword" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;">
                                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                    </span>
                                </div>
                                {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                <!-- Password Strength Bar -->
                                <div style="margin-bottom: 0.2rem;">
                                    <div class="progress" style="height: 5px; margin-bottom: 2px;">
                                        <div id="passwordStrengthBar" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%; transition: width 0.3s;"></div>
                                    </div>
                                    <small id="passwordStrengthText" class="fw-semibold" style="margin-top: -2px; display: block; font-size: 0.95em;"></small>
                                </div>

                            </div>
                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}" style="position:relative;">
                                <label for="password_confirmation">
                                    <x-icon type="password" />
                                    {{ trans('admin/users/table.password_confirm')  }}</label>
                                <div style="position:relative;">
                                    <input type="password" class="form-control pr-5" name="password_confirmation" id="password_confirmation" aria-label="password_confirmation" style="padding-right:2.5rem;">
                                    <span class="toggle-password" id="togglePasswordConfirm" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;">
                                        <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                    </span>
                                </div>
                                {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                <div id="passwordMatch" class="text-danger" style="display:none;font-size:0.95em;margin-top:2px;"></div>
                            </div>
                            <!-- Password Requirements -->
                            <h6 class="text-body mt-3">Password Requirements:</h6>
                            <ul id="pwdRequirements" class="ps-3 mb-3">
                                <li class="requirement-item" data-key="length">At least 12 characters</li>
                                <li class="requirement-item" data-key="uppercase">At least one uppercase letter</li>
                                <li class="requirement-item" data-key="lowercase">At least one lowercase letter</li>
                                <li class="requirement-item" data-key="digit">At least one digit</li>
                                <li class="requirement-item" data-key="symbol">At least one symbol (!@#$.,&)</li>
                                <li class="requirement-item" data-key="restricted">Must NOT contain: ' " % * - ( ) = + ~</li>
                            </ul>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-lg btn-primary btn-block" id="resetBtn" disabled>
                            {{ trans('auth/general.reset_password')  }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Real-time password requirements checker
function checkPasswordRequirements(password) {
    const requirements = {
        length: password.length >= 12,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        digit: /[0-9]/.test(password),
        symbol: /[!@#$.,&]/.test(password),
        restricted: !(/[\'"%*\-()=+~]/.test(password)),
    };
    return requirements;
}

function getPasswordStrength(password) {
    let score = 0;
    const reqs = checkPasswordRequirements(password);
    Object.values(reqs).forEach(v => { if (v) score += 1; });
    return Math.round((score / 6) * 100);
}

function updatePasswordStrength(password) {
    const bar = document.getElementById('passwordStrengthBar');
    const text = document.getElementById('passwordStrengthText');
    const strength = getPasswordStrength(password);
    bar.style.width = strength + '%';
    bar.setAttribute('aria-valuenow', strength);
    if (strength < 50) {
        bar.className = 'progress-bar bg-danger';
        text.textContent = 'Weak';
        text.style.color = '#dc3545';
    } else if (strength < 83) {
        bar.className = 'progress-bar bg-warning';
        text.textContent = 'Medium';
        text.style.color = '#fd7e14';
    } else {
        bar.className = 'progress-bar bg-success';
        text.textContent = 'Strong';
        text.style.color = '#198754';
    }
}

function updateRequirements(password) {
    const reqs = checkPasswordRequirements(password);
    document.querySelectorAll('#pwdRequirements .requirement-item').forEach(function(item) {
        const key = item.getAttribute('data-key');
        if (reqs[key]) {
            item.style.color = 'green';
        } else {
            item.style.color = 'red';
        }
    });
}

function allRequirementsMet(password) {
    const reqs = checkPasswordRequirements(password);
    return Object.values(reqs).every(Boolean);
}

function passwordsMatch(password, confirm) {
    return password === confirm && password.length > 0;
}

function updateButtonState() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const resetBtn = document.getElementById('resetBtn');
    const matchMsg = document.getElementById('passwordMatch');
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    let enable = true;
    if (!password || !confirm || !allRequirementsMet(password) || !passwordsMatch(password, confirm)) {
        enable = false;
    }
    resetBtn.disabled = !enable;
    // Show/hide match message
    if (confirm && password !== confirm) {
        matchMsg.style.display = 'block';
        matchMsg.textContent = 'Passwords do not match.';
    } else {
        matchMsg.style.display = 'none';
        matchMsg.textContent = '';
    }
}

function setupPasswordToggles() {
    function toggle(inputId, iconId) {
        const input = document.getElementById(inputId);
        const iconSpan = document.getElementById(iconId);
        if (!input || !iconSpan) return;
        iconSpan.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                iconSpan.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
            } else {
                input.type = 'password';
                iconSpan.innerHTML = '<i class="fas fa-eye-slash" aria-hidden="true"></i>';
            }
        });
    }
    toggle('password', 'togglePassword');
    toggle('password_confirmation', 'togglePasswordConfirm');
}

document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const form = document.querySelector('form[method="POST"][action*="/password/reset"]');
    updatePasswordStrength(passwordInput.value);
    updateRequirements(passwordInput.value);
    setupPasswordToggles();
    passwordInput.addEventListener('input', function() {
        updatePasswordStrength(passwordInput.value);
        updateRequirements(passwordInput.value);
        updateButtonState();
    });
    confirmInput.addEventListener('input', function() {
        updateButtonState();
    });
    updateButtonState();

    // Intercept form submit to show alert if token expired (simulate backend error)
    if (form) {
        form.addEventListener('submit', function(e) {
            // This block is a placeholder. Replace with real backend error check if available.
            // If backend returns token expired, show alert and prevent submit.
            // Example: if (window.resetTokenExpired) { ... }
            if (window.resetTokenExpired) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Link Expired',
                    text: 'Your reset password link has expired. Please request a new one.',
                    confirmButtonColor: '#d33'
                });
            }
        });
    }
});
</script>
@endpush

@stop


