<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */

if (!zen_is_superuser() && !check_page(FILENAME_SALEMAKER, '')) {
    return;
}

// prepare data
// use cached queries (1800s) to keep the dashboard fast
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_SPECIALS . " WHERE status = 0", false, true, 1800);
$specials_exp = $result->fields['count'];
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_SPECIALS . " WHERE status = 1", false, true, 1800);
$specials_act = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_FEATURED . " WHERE status = 0", false, true, 1800);
$featured_exp = $result->fields['count'];
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_FEATURED . " WHERE status = 1", false, true, 1800);
$featured_act = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status = 0", false, true, 1800);
$salemaker_exp = $result->fields['count'];
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_SALEMAKER_SALES . " WHERE sale_status = 1", false, true, 1800);
$salemaker_act = $result->fields['count'];
?>


    <div class="panel widget-wrapper">
        <div class="panel-heading">
            <i class="fa fa-tags"></i> <?= DASHBOARD_SALES ?>
        </div>

        <ul class="list-group">
            <li class="list-group-item">
                <a class="link-text" href="<?= zen_href_link(FILENAME_SPECIALS) ?>"><?= BOX_SPECIALS_SPECIALS ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?= BOX_LABEL_ACTIVE ?>" data-toggle="tooltip"><?= $specials_act ?></span>
                    <span class="label label-default" title="<?= BOX_LABEL_EXPIRED ?>" data-toggle="tooltip"><?= $specials_exp ?></span>
                </div>
            </li>

            <li class="list-group-item">
                <a class="link-text" href="<?= zen_href_link(FILENAME_FEATURED) ?>"><?= BOX_SPECIALS_FEATURED ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?= BOX_LABEL_ACTIVE ?>" data-toggle="tooltip"><?= $featured_act ?></span>
                    <span class="label label-default" title="<?= BOX_LABEL_EXPIRED ?>" data-toggle="tooltip"><?= $featured_exp ?></span>
                </div>
            </li>

            <li class="list-group-item">
                <a class="link-text" href="<?= zen_href_link(FILENAME_SALEMAKER) ?>"><?= BOX_SPECIALS_SALEMAKER ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?= BOX_LABEL_ACTIVE ?>" data-toggle="tooltip"><?= $salemaker_act ?></span>
                    <span class="label label-default" title="<?= BOX_LABEL_EXPIRED ?>" data-toggle="tooltip"><?= $salemaker_exp ?></span>
                </div>
            </li>
        </ul>

        <div class="panel-footer text-center">
            <small class="text-muted">
                <span class="text-success"><i class="fa fa-square"></i> <?= BOX_LABEL_ACTIVE ?></span>
                <span class="label-inactive-text"><i class="fa fa-square"></i> <?= BOX_LABEL_EXPIRED ?></span>
            </small>
        </div>
    </div>
