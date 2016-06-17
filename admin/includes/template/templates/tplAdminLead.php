<?php
/**
 * Admin Lead Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
?>

<section class="content-header row">
    <h1 class="pull-left"><?php echo $tplVars['leadDefinition']['pageTitle']; ?></h1>
    <?php if ($tplVars['listingBox']['paginator']['show']) { ?>
    <div class="form pull-right">
        <label for="paginationQueryLimit"><?php echo TEXT_PAGINATION_LIMIT_SELECT; ?></label>
        <?php echo zen_draw_pull_down_menu('paginationQueryLimit', $tplVars['leadDefinition']['paginationLimitSelect'], $tplVars['leadDefinition']['paginationLimitDefault'], 'id="paginationQueryLimit" style="width:auto"')?>
    </div>
    <?php } ?>
</section>
<?php if ($tplVars['leadDefinition']['headerTemplate']) { ?>
<section class="content-header row">
    <?php require 'includes/template/partials/'.$tplVars['leadDefinition']['headerTemplate']; ?>
</section>
<?php } ?>

<section class="row" id="adminLeadContainer">
    <aside class="col-md-2">
        <div class="panel">
<?php if (count($tplVars['leadDefinition']['actionLinks'])) { ?>
        <div class="panel">
<?php foreach ($tplVars['leadDefinition']['actionLinks'] as $actionLink) { ?>
            <a href="<?php echo $actionLink['href']; ?>" class="btn btn-primary btn-block"><?php echo $actionLink['text']; ?></a>
<?php }?>
        </div>
<?php } ?>
<?php if (count($tplVars['leadDefinition']['relatedLinks'])) { ?>
        <div class="panel">
        <h2><?php echo TEXT_LEAD_RELATED; ?></h2>
<?php foreach ($tplVars['leadDefinition']['relatedLinks'] as $relatedLink) { ?>
            <a href="<?php echo $relatedLink['href']; ?>" class="btn btn-primary btn-block" <?php if (isset($relatedLink['target'])) {?> target="<?php echo $relatedLink['target']; ?>" <?php } ?>>
<?php echo $relatedLink['text']; ?></a>
<?php }?>
        </div>
<?php } ?>
        </div>
    </aside>
    <section class="col-md-10">
        <div class="panel">
<?php require 'includes/template/partials/'.$tplVars['leadDefinition']['contentTemplate']; ?>
        </div>
    </section>
</section>

<div class="modal fade" tabindex="-1" role="dialog" id="rowMultiDeleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo TEXT_CONFIRM_DELETE; ?></h4>
            </div>
            <div class="modal-body">
                <p><?php echo TEXT_CONFIRM_DELETE_TEXT; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo TEXT_CANCEL; ?></button>
                <button type="button" class="btn btn-primary" id="rowMultiDeleteConfirm"><?php echo TEXT_CONFIRM; ?></button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php require 'includes/template/javascript/adminLeadCommon.php'; ?>
