<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_SALEMAKER, '')) return;

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

<div class="col-md-3 col-sm-6">
    <div class="panel widget-wrapper">
        <div class="panel-heading">
            <i class="fa fa-tags"></i> <?php echo DASHBOARD_SALES; ?>
        </div>

        <ul class="list-group">
            <li class="list-group-item">
                <a href="<?php echo zen_href_link(FILENAME_SPECIALS); ?>" style="color: #444; font-weight: 600;"><?php echo BOX_SPECIALS_SPECIALS; ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?php echo BOX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $specials_act; ?></span>
                    <span class="label label-default" title="<?php echo BOX_LABEL_EXPIRED; ?>" data-toggle="tooltip"><?php echo $specials_exp; ?></span>
                </div>
            </li>

            <li class="list-group-item">
                <a href="<?php echo zen_href_link(FILENAME_FEATURED); ?>" style="color: #444; font-weight: 600;"><?php echo BOX_SPECIALS_FEATURED; ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?php echo BOX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $featured_act; ?></span>
                    <span class="label label-default" title="<?php echo BOX_LABEL_EXPIRED; ?>" data-toggle="tooltip"><?php echo $featured_exp; ?></span>
                </div>
            </li>

            <li class="list-group-item">
                <a href="<?php echo zen_href_link(FILENAME_SALEMAKER); ?>" style="color: #444; font-weight: 600;"><?php echo BOX_SPECIALS_SALEMAKER; ?></a>
                <div class="pull-right">
                    <span class="label label-success" title="<?php echo BOX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $salemaker_act; ?></span>
                    <span class="label label-default" title="<?php echo BOX_LABEL_EXPIRED; ?>" data-toggle="tooltip"><?php echo $salemaker_exp; ?></span>
                </div>
            </li>
        </ul>

        <div class="panel-footer text-center" style="background: #fff; padding: 8px;">
            <small class="text-muted"><span class="text-success">■ <?php echo BOX_LABEL_ACTIVE; ?></span> &nbsp; <span style="color: #777;">■ <?php echo BOX_LABEL_EXPIRED; ?></span></small>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
