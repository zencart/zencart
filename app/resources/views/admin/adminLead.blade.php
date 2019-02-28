<?php
/**
 * Admin Lead Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>
@extends('layouts/master')

@section('content')

<section class="content-header row">
    <h1 class="pull-left">{{$tplVars['pageDefinition']['pageTitle']}}</h1>
    @if ($tplVars['listingBox']['paginator']['show'])
    <div class="form pull-right">
        <label for="paginationQueryLimit">{{trans('admin/pagination.limit-select')}}</label>
        <?php echo zen_draw_pull_down_menu('paginationQueryLimit', $tplVars['pageDefinition']['paginationLimitSelect'], $tplVars['pageDefinition']['paginationLimitDefault'], 'id="paginationQueryLimit" style="width:auto"')?>
    </div>
    @endif
</section>


@if ($tplVars['pageDefinition']['headerTemplate'])
<section class="content-header row">
    @include($tplVars['pageDefinition']['headerTemplate'])
</section>
@endif

<section class="row" id="adminLeadContainer">
    <aside class="col-md-2">
        <div class="panel">
            @if (count($tplVars['pageDefinition']['actionLinks']))
            <div class="panel">
            @foreach ($tplVars['pageDefinition']['actionLinks'] as $actionLink)
            <a href="{{$actionLink['href']}}" class="btn btn-primary btn-block">{{$actionLink['text'] }}</a>
            @endforeach
            </div>
            @endif
            @if (count($tplVars['pageDefinition']['relatedLinks']))
            <div class="panel">
            <h2>{{trans('admin/lead-common.title-related-items')}}</h2>
            @foreach ($tplVars['pageDefinition']['relatedLinks'] as $relatedLink)
            <a href="{{ $relatedLink['href'] }}" class="btn btn-primary btn-block"
               @if (isset($relatedLink['target']))  target="{{ $relatedLink['target'] }}" @endif>
            {{$relatedLink['text']}}
            </a>
            @endforeach
        </div>
        @endif
        </div>
    </aside>
    <section class="col-md-10">
        <div class="panel">
        @include($tplVars['pageDefinition']['contentTemplate'])
        </div>
    </section>
</section>

<div class="modal fade" tabindex="-1" role="dialog" id="rowMultiDeleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{trans('admin/button-forms.button-confirm-delete')}}</h4>
            </div>
            <div class="modal-body">
                <p>{{trans('admin/button-forms.text-confirm-delete')}}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{trans('admin/button-forms.text-cancel')}}</button>
                <button type="button" class="btn btn-primary" id="rowMultiDeleteConfirm">{{trans('admin/button-forms.text-confirm')}}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php require 'includes/template/javascript/adminLeadCommon.php'; ?>
@if (count($tplVars['pageDefinition']['extraHandlerTemplates']))
    @foreach ($tplVars['pageDefinition']['extraHandlerTemplates'] as $template)
        <?php require_once('includes/template/' . $template); ?>
    @endforeach
@endif
@endsection