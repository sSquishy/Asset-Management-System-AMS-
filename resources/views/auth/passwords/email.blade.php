@extends('layouts/basic')

@section('content')

<style>
    /* Match login UI layout */
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
</style>

<!-- Same background as login -->
<div class="login-bg" aria-hidden="true"
    style="position:fixed;inset:0;z-index:800;background-image:url('{{ asset('custom/img/loginbg.jpg') }}');
     background-size:cover;background-position:center;background-repeat:no-repeat;pointer-events:none;">
</div>

@if ($snipeSettings->custom_forgot_pass_url)

<!-- LDAP external reset link -->
<div class="container login-container">
    <div class="row">
        <div class="col-md-4">

            <div class="box login-box">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('auth/general.ldap_reset_password') }}</h2>
                </div>

                <div class="login-box-body text-center" style="padding: 25px;">
                    <a href="{{ $snipeSettings->custom_forgot_pass_url }}" class="btn btn-primary btn-lg btn-block" rel="noopener">
                        {{ trans('auth/general.ldap_reset_password') }}
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@else

<form class="form" role="form" method="POST" action="{{ url('/password/email') }}">
    {!! csrf_field() !!}

    <div class="container login-container">

        <div class="row">
            <div class="col-md-4">

                <div class="box login-box">

                    <div class="box-header with-border">
                        <h1 class="box-title">{{ trans('auth/general.send_password_link') }}</h1>
                    </div>

                    <div class="login-box-body">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <x-icon type="info-circle" />
                                    {!! trans('auth/general.username_help_top') !!}
                                </div>
                            </div>
                        </div>

                        @include('notifications')

                        <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                            <label for="username">
                                <x-icon type="user" />
                                {{ trans('admin/users/table.username') }}
                            </label>
                            <input type="text" class="form-control" name="username" value="{{ old('username') }}"
                                placeholder="{{ trans('admin/users/table.username') }}">
                            {!! $errors->first('username', '<span class="alert-msg"><i class="fas fa-times"></i> :message</span>') !!}
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <br>
                                <!-- show help text toggle -->
                                <a href="#" id="show">
                                    <x-icon type="caret-right" />
                                    {{ trans('general.show_help') }}
                                </a>

                                <!-- hide help text -->
                                <a href="#" id="hide" style="display:none">
                                    <x-icon type="caret-up" />
                                    {{ trans('general.hide_help') }}
                                </a>

                                <!-- help text -->
                                <p class="help-block" id="help-text" style="display:none">
                                    {!! trans('auth/general.username_help_bottom') !!}
                                </p>

                            </div>
                        </div>

                    </div>

                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-block" style="font-size:1.25rem;padding-top:0.75rem;padding-bottom:0.75rem;">
                            {{ trans('auth/general.email_reset_password') }}
                        </button>
                        <div class="text-center mt-2">
                            <a href="{{ url('/login') }}" class="btn btn-link" style="text-decoration:none;color:#337ab7;font-weight:500;">Back to Login</a>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

</form>

@endif

@endsection

@push('js')
<script nonce="{{ csrf_token() }}">
    $(document).ready(function() {
        $("#show").click(function() {
            $("#help-text").fadeIn(500);
            $("#show").hide();
            $("#hide").show();
        });

        $("#hide").click(function() {
            $("#help-text").fadeOut(300);
            $("#show").show();
            $("#hide").hide();
        });
    });
</script>
@endpush