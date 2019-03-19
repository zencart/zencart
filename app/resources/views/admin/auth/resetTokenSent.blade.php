@extends('layouts/auth')

@section('auth')
    <div id="loginFormDiv" class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h1>{{ trans('admin/auth.title-reset-link-sent') }}</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ trans('admin/auth.alert-reset-token-sent') }}
                    </div>
                    <a href="{{ zen_href_link(FILENAME_LOGIN) }}"
                       class="btn btn-primary px-4">{{ trans('admin/auth.button-login-submit') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
