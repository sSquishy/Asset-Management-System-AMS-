@extends('layouts/basic')


{{-- Page content --}}
@section('content')

<style>
    /* Center the login box vertically + horizontally without affecting page height */
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
        /* small breathing room on edges */
        box-sizing: border-box;
    }

    /* Mobile / small screens: keep the container in normal flow so it doesn't overlay content */
    @media (max-width: 767.98px) {
        .login-container {
            position: static;
            transform: none;
            padding-top: 24px;
            padding-bottom: 24px;
        }
    }

    /* Add 32px radius to the login box */
    .box.login-box {
        border-radius: 32px;
        overflow: hidden;
        background: #ffffff !important;
        /* ensure children respect the rounded corners */
    }

    .box-header .box-title {
        width: 100%;
        text-align: center;
    }
</style>

<!-- full-screen background using the same image asset -->
<div class="login-bg" aria-hidden="true" style="position:fixed;inset:0;z-index:800;background-image:url('{{ asset('custom/img/loginbg.jpg') }}');background-size:cover;background-position:center center;background-repeat:no-repeat;pointer-events:none;">
</div>

<form role="form" action="{{ url('/login') }}" method="POST" autocomplete="{{ (config('auth.login_autocomplete') === true) ? 'on' : 'off'  }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <!-- Chrome autofill hack -->
    <input type="text" name="prevent_autofill" id="prevent_autofill" value="" style="display:none;" aria-hidden="true">
    <input type="password" name="password_fake" id="password_fake" value="" style="display:none;" aria-hidden="true">

    <div class="container login-container">

        <div class="row">
            <div class="col-md-4 ">

                @if (($snipeSettings->google_login=='1') && ($snipeSettings->google_client_id!='') && ($snipeSettings->google_client_secret!=''))
                <br><br>
                <a href="{{ route('google.redirect')  }}" class="btn btn-block btn-social btn-google btn-lg">
                    <i class="fa-brands fa-google"></i>
                    {{ trans('auth/general.google_login') }}
                </a>

                <div class="separator">{{ strtoupper(trans('general.or')) }}</div>
                @endif

                <div class="box login-box">
                    <div class="box-header with-border">
                        <h1 class="box-title"> {{ trans('auth/general.login_prompt')  }}</h1>
                    </div>

                    <div class="login-box-body">
                        <div class="row">

                            @if ($snipeSettings->login_note)
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    {!! Helper::parseEscapedMarkedown($snipeSettings->login_note) !!}
                                </div>
                            </div>
                            @endif

                            <!-- Notifications -->
                            @include('notifications')

                            @if (!config('app.require_saml'))
                            <div class="col-md-12">
                                <fieldset name="login" aria-label="login">
                                    <legend></legend>

                                    <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                                        <label for="username">
                                            <x-icon type="user" />
                                            {{ trans('admin/users/table.username') }}
                                        </label>
                                        <input class="form-control" placeholder="{{ trans('admin/users/table.username') }}" name="username" type="text" id="username" autocomplete="{{ (config('auth.login_autofill') === true) ? 'on' : 'off'  }}" autofocus>
                                        {!! $errors->first('username', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>

                                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}" style="position:relative;">
                                        <label for="password">
                                            <x-icon type="password" />
                                            {{ trans('admin/users/table.password') }}
                                        </label>
                                        <div style="position:relative;">
                                            <input class="form-control pr-5" placeholder="{{ trans('admin/users/table.password') }}" name="password" type="password" id="password" autocomplete="{{ (config('auth.login_autofill') === true) ? 'on' : 'off'  }}" style="padding-right:2.5rem;">
                                            <span class="toggle-password" id="togglePasswordLogin" style="position:absolute;top:50%;right:10px;transform:translateY(-50%);cursor:pointer;font-size:1.2em;color:#888;z-index:2;">
                                                <i class="fas fa-eye-slash" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                    </div>

                                    <div class="form-group" style="margin-bottom: 0.25rem;">
                                        <label class="form-control" style="padding-bottom: 0.25rem;">
                                            <input name="remember" type="checkbox" value="1" id="remember"> {{ trans('auth/general.remember_me') }}
                                        </label>
                                    </div>
                                </fieldset>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="box-footer" style="">
                        @if (config('app.require_saml'))
                        <a class="btn btn-primary btn-block" href="{{ route('saml.login') }}">{{ trans('auth/general.saml_login') }}</a>
                        @else
                        <button class="btn btn-primary btn-block" type="submit" id="submit">
                            {{ trans('auth/general.login') }}
                        </button>
                        @endif

                            @if ($snipeSettings->custom_forgot_pass_url)
                            <div class="text-center" style="margin-top: 8px;">
                                <a href="{{ $snipeSettings->custom_forgot_pass_url }}" rel="noopener" class="btn btn-link" style="text-decoration:none;color:#337ab7;font-weight:500;">{{ trans('auth/general.forgot_password') }}</a>
                            </div>
                            @elseif (!config('app.require_saml'))
                            <div class="text-center" style="margin-top: 8px;">
                                <a href="{{ route('password.request') }}" class="btn btn-link" style="text-decoration:none;color:#337ab7;font-weight:500;">{{ trans('auth/general.forgot_password') }}</a>
                            </div>
                            @endif
                    </div>

                </div> <!-- end login box -->

            </div>
        </div>
    </div>


@push('js')
<script>
// Password eye toggle for login
document.addEventListener('DOMContentLoaded', function() {
    var pwInput = document.getElementById('password');
    var toggle = document.getElementById('togglePasswordLogin');
    if (pwInput && toggle) {
        toggle.addEventListener('click', function() {
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                toggle.innerHTML = '<i class="fas fa-eye" aria-hidden="true"></i>';
            } else {
                pwInput.type = 'password';
                toggle.innerHTML = '<i class="fas fa-eye-slash" aria-hidden="true"></i>';
            }
        });
    }
});
</script>
@endpush
</form>

</img>
@stop