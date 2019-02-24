@extends('layouts/auth')

@section('auth')
    <div id="loginFormDiv" class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title col-md-6">{{ trans('admin/auth.title-login') }}</h1>
            </div>
            <div class="card-body">
                <?php
                echo zen_draw_form(
                    'loginForm', FILENAME_LOGIN, zen_get_all_get_params(), 'post',
                    'id="loginForm" class="form-horizontal"', 'true');
                echo zen_draw_hidden_field('action', 'doLogin');
                ?>
                @include('partials.common.form-errors')


                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                    </div>
                    <?php echo zen_draw_input_field(
                        'admin_name', zen_output_string($tplVars['adminName']),
                        'class="form-control" id="admin_name" autocomplete="off" autofocus placeholder="' . trans(
                            'admin/auth.placeholder-admin-name') . '"'); ?>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    </div>
                    <?php echo zen_draw_password_field(
                        'admin_pass', '', false,
                        'class="form-control" id="admin_pass" placeholder="' . trans(
                            'admin/auth.placeholder-admin-password') . '"', false); ?>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button type="submit"
                                class="btn btn-primary px-4">{{ trans('admin/auth.button-login-submit') }}</button>
                    </div>
                    <div class="col-6 text-right">
                        <a href="<?php echo zen_href_link(FILENAME_PASSWORD_FORGOTTEN); ?>"
                           class="btn btn-link px-0">{{ trans('admin/auth.forgotten-password-prompt') }}</a>
                    </div>
                </div>
                </form>
                <div id="loginExpiryPolicy">{{ trans('admin/auth.password-expiry-notice') }}</div>
            </div>
        </div>
    </div>
@endsection
