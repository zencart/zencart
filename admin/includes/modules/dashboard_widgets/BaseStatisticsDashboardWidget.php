<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
 * @author ZenExpert - https://zenexpert.com
 */

// permission checks
$show_customers = (zen_is_superuser() || check_page(FILENAME_CUSTOMERS, ''));
$show_products  = (zen_is_superuser() || check_page(FILENAME_PRODUCT, ''));

// prepare data
// use cached queries (1800s) to keep the dashboard fast

// customers
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_CUSTOMERS, false, true, 1800);
$customers = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_CUSTOMERS . " WHERE customers_newsletter = 1", false, true, 1800);
$newsletters = $result->fields['count'];

// products
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_PRODUCTS . " WHERE products_status = 1", false, true, 1800);
$products_on = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_PRODUCTS . " WHERE products_status = 0", false, true, 1800);
$products_off = $result->fields['count'];

// reviews
$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_REVIEWS, false, true, 1800);
$reviews = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_REVIEWS . " WHERE status = 0", false, true, 1800);
$reviews_pending = $result->fields['count'];

// counter
$counter = 0;
$counter_date = 'N/A';
$result = $db->Execute("SELECT startdate, counter FROM " . TABLE_COUNTER, false, true, 7200);
if ($result->RecordCount()) {
    $counter = $result->fields['counter'];
    $raw_date = $result->fields['startdate'];
    // format: "Since Jan 2003"
    $counter_date = date('M Y', mktime(0,0,0, substr($raw_date, 4, 2), 1, substr($raw_date, 0, 4)));
}
?>

<div class="col-md-3 col-sm-6">
    <div class="panel widget-wrapper">
        <div class="panel-heading">
            <i class="fa fa-hdd-o"></i> <?php echo BOX_TITLE_STORE_SNAPSHOT; ?>
        </div>

        <ul class="list-group">

            <?php if ($show_products) { ?>
                <li class="list-group-item">
                    <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER); ?>" style="color:#555; font-weight:600;"><?php echo BOX_TITLE_PRODUCTS; ?></a>
                    <div class="pull-right">
                        <span class="label label-success" title="<?php echo BOX_LABEL_ACTIVE; ?>" data-toggle="tooltip"><?php echo $products_on; ?></span>
                        <span class="label label-default" title="<?php echo BOX_LABEL_INACTIVE; ?>" data-toggle="tooltip"><?php echo $products_off; ?></span>
                    </div>
                </li>
            <?php } ?>

            <?php if ($show_customers) { ?>
                <li class="list-group-item">
                    <a href="<?php echo zen_href_link(FILENAME_CUSTOMERS); ?>" style="color:#555; font-weight:600;"><?php echo BOX_TITLE_CUSTOMERS; ?></a>
                    <div class="pull-right">
                        <span class="label label-info" title="<?php echo BOX_LABEL_TOTAL_ACCOUNTS; ?>" data-toggle="tooltip"><?php echo $customers; ?></span>
                        <span class="label label-warning" title="<?php echo BOX_LABEL_NEWSLETTER_SUBSCRIBERS; ?>" data-toggle="tooltip"><i class="fa fa-envelope"></i> <?php echo $newsletters; ?></span>
                    </div>
                </li>
            <?php } ?>

            <li class="list-group-item">
                <a href="<?php echo zen_href_link(FILENAME_REVIEWS); ?>" style="color:#555; font-weight:600;"><?php echo BOX_TITLE_REVIEWS; ?></a>
                <div class="pull-right">
                    <span class="label label-primary" title="<?php echo BOX_LABEL_TOTAL_REVIEWS; ?>" data-toggle="tooltip"><?php echo $reviews; ?></span>
                    <?php if ($reviews_pending > 0) { ?>
                        <span class="label label-danger" title="<?php echo BOX_LABEL_REVIEWS_PENDING; ?>" data-toggle="tooltip"><?php echo $reviews_pending; ?></span>
                    <?php } ?>
                </div>
            </li>

            <li class="list-group-item">
                <span style="color:#555; font-weight:600;"><?php echo BOX_TITLE_TOTAL_VISITS; ?></span>
                <small class="text-muted" style="font-size: 10px; display:block; line-height:1;">Since <?php echo $counter_date; ?></small>
                <div class="pull-right" style="margin-top: -15px;">
                     <span class="badge" style="background:#eee; color:#555; font-weight:normal; font-size: 11px;">
                        <?php echo number_format($counter); ?>
                     </span>
                </div>
            </li>

        </ul>
        <div class="panel-footer text-center" style="background: #fff; padding: 8px;">
            <small class="text-muted">
                <span class="text-success">■ <?php echo BOX_LABEL_ACTIVE; ?></span> &nbsp;
                <span style="color:#999;">■ <?php echo BOX_LABEL_INACTIVE; ?></span>
            </small>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
