<?php
/**
 * Admin Home Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
?>
<section class="content-header row">
    <h1 class="pull-left"><?php echo $tplVars['pageDefinition']['pageTitle']; ?></h1>
    <div class="form pull-right">
        <label for="paginationQueryLimit"><?php echo TEXT_PAGINATION_LIMIT_SELECT; ?></label>
        <?php echo zen_draw_pull_down_menu('paginationQueryLimit', $tplVars['pageDefinition']['paginationLimitSelect'], $tplVars['pageDefinition']['paginationLimitDefault'], 'id="paginationQueryLimit" style="width:auto"')?>
    </div>
</section>
<?php if ($tplVars['pageDefinition']['headerTemplate']) { ?>
    <section class="content-header row">
        <?php require 'includes/template/partials/'.$tplVars['pageDefinition']['headerTemplate']; ?>
    </section>
<?php } ?>
<section class="row" id="adminLeadContainer">
    <?php if (count($tplVars['pageDefinition']['relatedLinks'])) { ?>
    <aside class="col-md-2">
        <div class="panel">
            <?php if (count($tplVars['pageDefinition']['relatedLinks'])) { ?>
                <div class="panel">
                    <h2><?php echo TEXT_LEAD_RELATED; ?></h2>
                    <div class="btn-group-vertical">
                        <?php foreach ($tplVars['pageDefinition']['relatedLinks'] as $relatedLink) { ?>
                            <a href="<?php echo $relatedLink['href']; ?>"
                               class="btn btn-primary" <?php if (isset($relatedLink['target'])) {?> target="<?php echo $relatedLink['target']; ?>" <?php } ?>>
                                <?php echo $relatedLink['text']; ?></a>
                        <?php }?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </aside>
    <?php } ?>

    <section class="">
        <div class="panel">
            <?php require 'includes/template/partials/'.$tplVars['pageDefinition']['contentTemplate']; ?>
        </div>
    </section>
</section>

<?php require 'includes/template/javascript/adminLeadCommon.php'; ?>
