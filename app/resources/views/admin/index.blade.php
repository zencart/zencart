@extends('layouts/master')

@section('content')
    <div class="row">
        <h1><a href="#" class="widget-add">{{ TEXT_DASHBOARD_ADD_WIDGETS }}</a></h1>
    </div>
    <div id="main-widget-container">
        @include('partials/dashboardWidgets')
    </div>

    <div id="add-widget" class="modal fade " tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ TITLE_MODAL_DASHBOARD_ADD_WIDGETS }}</h4>
                </div>
                <div class="modal-body add-widget-container">
                </div>
            </div>
        </div>
    </div>

    <div id="widget-settings" class="modal fade " tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content widget-settings-container" >
            </div>
        </div>
    </div>

@endsection
