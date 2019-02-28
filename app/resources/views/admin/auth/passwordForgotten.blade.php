@extends('layouts/auth')

@section('auth')
    <div id="loginFormDiv" class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">{{ trans('admin/auth.title-password-forgotten') }}</h1>
            </div>
            <div class="card-body">
                <?php
                echo zen_draw_form(
                    'loginForm', FILENAME_PASSWORD_FORGOTTEN, zen_get_all_get_params(), 'post',
                    'id="loginForm" class="form-horizontal"', 'true');
                echo zen_draw_hidden_field('action', 'doReset');
                ?>
                @include('partials.common.form-errors')

                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                    </div>
                    <?php echo zen_draw_input_field(
                        'admin_email', '',
                        'class="form-control" id="admin_email" autocomplete="off" autofocus placeholder="' . trans(
                            'admin/auth.placeholder-admin-password-forgotten') . '"'); ?>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button type="submit"
                                class="btn btn-primary px-4">{{ trans('admin/auth.button-password-forgotten-submit') }}</button>
                    </div>
                    <a href="{{ zen_href_link(FILENAME_LOGIN) }}"
                       class="btn btn-primary px-4">{{ trans('admin/button-forms.text-cancel') }}</a>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection
