@extends('layouts/auth')

@section('auth')
    <div id="loginFormDiv" class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h1>{{ trans('admin/auth.title-login-expired') }}</h1>
            </div>
            <div class="card-body">
                <?php
                echo zen_draw_form(
                    'loginForm', FILENAME_LOGIN, '', 'post', 'id="loginForm" class="form-horizontal"', 'true');
                echo zen_draw_hidden_field('action', 'doExpiredLogin', 'id="action1"');
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
                        'oldpwd', '', false,
                        'class="form-control" id="old_pwd" placeholder="' . trans(
                            'admin/auth.placeholder-admin-oldpwd') . '"', false); ?>
                </div>

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    </div>
                    <?php echo zen_draw_password_field(
                        'newpwd', '', false,
                        'class="form-control" id="admin_pass" placeholder="' . trans(
                            'admin/auth.placeholder-admin-newpwd') . '"', false); ?>

                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                    </div>
                    <?php echo zen_draw_password_field(
                        'confpwd', '', false,
                        'class="form-control" id="admin_pass2" placeholder="' . trans(
                            'admin/auth.placeholder-admin-confirmpwd') . '"', false); ?>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button type="submit"
                                class="btn btn-primary px-4">{{ trans('admin/auth.button-login-expired-submit') }}</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection
