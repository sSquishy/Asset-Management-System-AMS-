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
                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <label for="password">
                                    <x-icon type="password" />
                                    {{ trans('admin/users/table.password')  }}
                                </label>
                                <input type="password" class="form-control" name="password" aria-label="password">
                                {!! $errors->first('password', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <label for="password_confirmation">
                                    <x-icon type="password" />
                                    {{ trans('admin/users/table.password_confirm')  }}</label>
                                <input type="password" class="form-control" name="password_confirmation" aria-label="password_confirmation">
                                {!! $errors->first('password_confirmation', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-lg btn-primary btn-block">
                            {{ trans('auth/general.reset_password')  }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@stop


