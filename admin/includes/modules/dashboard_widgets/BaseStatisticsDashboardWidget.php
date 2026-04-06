<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
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
    $counter_date = $zcDate->output(DATE_FORMAT_SHORT_NO_DAY, mktime(0, 0, 0, (int)substr($raw_date, 4, 2), (int)substr($raw_date, -2), (int)substr($raw_date, 0, 4)));
}
?>


    <div class="panel widget-wrapper">
        <div class="panel-heading">
            <i class="fa fa-hdd-o"></i> <?= BOX_TITLE_STORE_SNAPSHOT ?>
        </div>

        <ul class="list-group">

            <?php if ($show_products) { ?>
                <li class="list-group-item">
                    <a class="link-text" href="<?= zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER) ?>"><?= BOX_TITLE_PRODUCTS ?></a>
                    <div class="pull-right">
                        <span class="label label-success" title="<?= BOX_LABEL_ACTIVE ?>" data-toggle="tooltip"><?= $products_on ?></span>
                        <span class="label label-default" title="<?= BOX_LABEL_INACTIVE ?>" data-toggle="tooltip"><?= $products_off ?></span>
                    </div>
                </li>
            <?php } ?>

            <?php if ($show_customers) { ?>
                <li class="list-group-item">
                    <a class="link-text" href="<?= zen_href_link(FILENAME_CUSTOMERS) ?>"><?= BOX_TITLE_CUSTOMERS ?></a>
                    <div class="pull-right">
                        <span class="label label-info" title="<?= BOX_LABEL_TOTAL_ACCOUNTS ?>" data-toggle="tooltip"><?= $customers ?></span>
                        <span class="label label-warning" title="<?= BOX_LABEL_NEWSLETTER_SUBSCRIBERS ?>" data-toggle="tooltip"><i class="fa fa-envelope"></i> <?= $newsletters ?></span>
                    </div>
                </li>
            <?php } ?>

            <li class="list-group-item">
                <a class="link-text" href="<?= zen_href_link(FILENAME_REVIEWS) ?>"><?= BOX_TITLE_REVIEWS ?></a>
                <div class="pull-right">
                    <span class="label label-primary" title="<?= BOX_LABEL_TOTAL_REVIEWS ?>" data-toggle="tooltip"><?= $reviews ?></span>
                    <?php if ($reviews_pending > 0) { ?>
                        <span class="label label-danger" title="<?= BOX_LABEL_REVIEWS_PENDING ?>" data-toggle="tooltip"><?= $reviews_pending ?></span>
                    <?php } ?>
                </div>
            </li>

            <li class="list-group-item">
                <span class="link-text"><?= BOX_TITLE_TOTAL_VISITS ?></span>
                <small class="text-muted"><?= sprintf(TEXT_SINCE_DATE, $counter_date) ?></small>
                <div class="pull-right" style="margin-top: -15px;">
                     <span class="badge base-counter-badge">
                        <?= number_format($counter) ?>
                     </span>
                </div>
            </li>

        </ul>
        <div class="panel-footer text-center">
            <small class="text-muted">
                <span class="text-success"><i class="fa fa-square"></i> <?= BOX_LABEL_ACTIVE ?></span> &nbsp;
                <span class="label-inactive-text"><i class="fa fa-square"></i> <?= BOX_LABEL_INACTIVE ?></span>
            </small>
        </div>
    </div>
