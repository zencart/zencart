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
<div class="container" id="adminLeadContainer">
  <div class="row" style="max-width: 100%; border-bottom: 1px solid">
    <div class="row">
      <div class="left small-9 columns">
        <h1><?php echo $tplVars['leadDefinition']['pageTitle']; ?></h1>
      </div>
      <div class="small-1 columns">
        <label class="inline" for="paginationQueryLimit"><?php echo TEXT_PAGINATION_LIMIT_SELECT; ?></label>
      </div>
      <div class="small-2 columns">
       <?php echo zen_draw_pull_down_menu('paginationQueryLimit', $tplVars['leadDefinition']['paginationLimitSelect'], $tplVars['leadDefinition']['paginationLimitDefault'], 'id="paginationQueryLimit" style="width:auto"')?>
      </div>
    </div>
    <?php if ($tplVars['leadDefinition']['headerTemplate']) { ?>
    <div class="left small-9 columns">
      <?php require 'includes/template/partials/'.$tplVars['leadDefinition']['headerTemplate']; ?>
    </div>
    <?php } ?>
  </div>

  <div class="row" style="max-width: 100%">
    <div class="large-2 columns" style="border-right: 1px solid">
      <div class="panel multi">
        <div class="panel">
          <?php foreach ($tplVars['leadDefinition']['actionLinks'] as $actionLink) { ?>
          <a
            href="<?php echo $actionLink['href']; ?>"
            class="button expand radius lead"><?php echo $actionLink['text']; ?></a><br>
          <?php }?>
        </div>
        <?php if (isset($tplVars['leadDefinition']['relatedLinks'])) { ?>
        <div class="panel">
          <h1><?php echo TEXT_LEAD_RELATED; ?></h1>
          <?php foreach ($tplVars['leadDefinition']['relatedLinks'] as $relatedLink) { ?>
          <a href="<?php echo $relatedLink['href']; ?>"
            class="button expand radius lead"><?php echo $relatedLink['text']; ?></a>
          <?php }?>
        </div>
        <?php } ?>
      </div>
    </div>
    <div id="adminLeadMainContent">
      <?php require 'includes/template/partials/'.$tplVars['leadDefinition']['contentTemplate']; ?>
    </div>
  </div>
</div>

<div id="rowMultiDeleteModal" class="reveal-modal small" data-reveal tabindex="-1">
    <div class="modal-header">
        <a class="close-reveal-modal">×</a>
        <h3><?php echo TEXT_CONFIRM_DELETE; ?></h3>
    </div>
    <div class="modal-body">
        <?php echo TEXT_CONFIRM_DELETE_TEXT; ?>
    </div>
    <div class="modal-footer">
        <a href="#" id="rowMultiDeleteConfirm" data-item=""><button
                class="radius button"><?php echo TEXT_CONFIRM; ?></button></a>
        <button class="radius button dismiss"><?php echo TEXT_CANCEL; ?></button>
    </div>
</div>
<?php require 'includes/template/javascript/adminLeadCommon.php'; ?>
