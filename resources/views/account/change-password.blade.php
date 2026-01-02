@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.changepassword') }}
@stop

{{-- Account page content --}}
@section('content')


<div class="row">
    <div class="col-md-9">
    <form method="POST" action="{{ route('account.password.update') }}" accept-charset="UTF-8" class="form-horizontal" autocomplete="off">
    <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="box box-default">
            <div class="box-body">


    <!-- Old Password -->
    <div class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}" style="position:relative;">
        <label for="current_password" class="col-md-3 control-label"> {{ trans('general.current_password') }} </label>
        <div class="col-md-5 required">
            <div style="position:relative;">
                <input class="form-control pr-5" type="password" name="current_password" id="current_password" required {{ (config('app.lock_passwords') ? ' disabled' : '') }} style="padding-right:2.5rem;">
                <span class="toggle-password" id="toggleCurrentPassword" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;">
                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                </span>
            </div>
            {!! $errors->first('current_password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
        </div>
    </div>


    <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}" style="position:relative;">
        <label for="password" class="col-md-3 control-label">{{ trans('general.new_password') }}</label>
        <div class="col-md-5 required">
            <div style="position:relative;">
                <input class="form-control pr-5" type="password" name="password" id="password" required {{ (config('app.lock_passwords') ? ' disabled' : '') }} style="padding-right:2.5rem;">
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
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
        </div>
    </div>

    <div class="form-group {{ $errors->has('password_confirmation') ? ' has-error' : '' }}" style="position:relative;">
        <label for="password_confirmation" class="col-md-3 control-label">Confirm Password</label>
        <div class="col-md-5 required">
            <div style="position:relative;">
                <input class="form-control pr-5" type="password" name="password_confirmation" id="password_confirmation"  {{ (config('app.lock_passwords') ? ' disabled' : '') }} aria-label="password_confirmation" style="padding-right:2.5rem;">
                <span class="toggle-password" id="togglePasswordConfirm" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;">
                    <i class="fas fa-eye-slash" aria-hidden="true"></i>
                </span>
            </div>
            {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            <div id="passwordMatch" class="text-danger" style="display:none;font-size:0.95em;margin-top:2px;"></div>
            @if (config('app.lock_passwords')===true)
                <p class="text-warning"><i class="fas fa-lock"></i> {{ trans('general.feature_disabled') }}</p>
            @endif
                <div id="currentPasswordMatch" class="text-danger" style="display:none;font-size:0.95em;margin-top:2px;"></div>
        </div>
    </div>

    <!-- Password Requirements -->
    <div class="form-group">
        <div class="col-md-5 col-md-offset-3">
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



            </div> <!-- .box-body -->
            <div class="box-footer text-right">
                <a class="btn btn-link" href="{{ URL::previous() }}">{{ trans('button.cancel') }}</a>
                <button type="submit" class="btn btn-primary" id="changePasswordBtn" disabled><x-icon type="checkmark" /> {{ trans('general.save') }}</button>
            </div>

        </div> <!-- .box-default -->
        </form>
    </div> <!-- .col-md-9 -->
</div> <!-- .row-->

@push('js')
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
    const changeBtn = document.getElementById('changePasswordBtn');
    const matchMsg = document.getElementById('passwordMatch');
    const password = passwordInput.value;
    const confirm = confirmInput.value;
    let enable = true;
    if (!password || !confirm || !allRequirementsMet(password) || !passwordsMatch(password, confirm)) {
        enable = false;
    }
    changeBtn.disabled = !enable;
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
    toggle('current_password', 'toggleCurrentPassword');
    toggle('password', 'togglePassword');
    toggle('password_confirmation', 'togglePasswordConfirm');
}

document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
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
});
</script>
@endpush

@stop
