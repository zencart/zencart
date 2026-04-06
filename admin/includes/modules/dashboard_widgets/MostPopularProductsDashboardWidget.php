<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 * Portions copyright 2026 ZenExpert - https://zenexpert.com
 */

if (!zen_is_superuser() && !check_page(FILENAME_STATS_PRODUCTS_PURCHASED, '')) {
    return;
}

// prepare data (last 30 days)
$sql = "SELECT p.products_id, pd.products_name, p.products_image, p.products_model, SUM(op.products_quantity) as total_sold
        FROM " . TABLE_ORDERS_PRODUCTS . " op
        JOIN " . TABLE_ORDERS . " o ON op.orders_id = o.orders_id
        JOIN " . TABLE_PRODUCTS . " p ON op.products_id = p.products_id
        JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (p.products_id = pd.products_id AND pd.language_id = " . (int)$_SESSION['languages_id'] . ")
        WHERE o.date_purchased >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY p.products_id, pd.products_name, p.products_image, p.products_model
        ORDER BY total_sold DESC";

$top_products = $db->Execute($sql, 5, true, 1800);
?>

<div class="panel widget-wrapper">
    <div class="panel-heading">
        <i class="fa fa-trophy"></i> <?= BOX_LABEL_TOP_SELLERS ?> <small class="text-muted"><?= BOX_LABEL_TOP_SELLERS_PERIOD ?></small>
    </div>

    <?php if ($top_products->RecordCount() > 0) { ?>
        <ul class="list-group">
            <?php foreach ($top_products as $top_product) {
                $pID = $top_product['products_id'];
                $name = $top_product['products_name'];
                $sold = $top_product['total_sold'];
                $img  = $top_product['products_image'];
                $model = zen_output_string_protected($top_product['products_model']);

                if (empty($img) || !file_exists(DIR_FS_CATALOG_IMAGES . $img)) {
                    // fallback placeholder if no image found
                    $thumb = '<div class="most-popular-fallback-image"><i class="fa fa-image"></i></div>';
                } else {
                    $thumb = zen_image(DIR_WS_CATALOG_IMAGES . $img, $name, IMAGE_SHOPPING_CART_WIDTH, IMAGE_SHOPPING_CART_HEIGHT, 'class="most-popular-main-image object-fit-contain"');
                }
                ?>
                <li class="list-group-item most-popular-item">
                    <div class="media">
                        <div class="media-left media-middle">
                            <a href="<?= zen_href_link(FILENAME_PRODUCT, 'action=new_product&pID=' . $pID) ?>">
                                <?= $thumb ?>
                            </a>
                        </div>
                        <div class="media-body media-middle">
                            <h4 class="media-heading">
                                <a href="<?= zen_href_link(FILENAME_PRODUCT, 'action=new_product&pID=' . $pID) ?>">
                                    <?= zen_trunc_string(zen_output_string_protected($name), 35, true) ?>
                                </a>
                            </h4>
                            <small class="text-muted"><?= BOX_LABEL_PRODUCTS_ID . $pID . (!empty($model) ? ' | ' . BOX_LABEL_PRODUCTS_MODEL . $model : '' ) ?></small>
                        </div>
                        <div class="media-right media-middle text-right">
                        <span class="badge bg-green">
                            <?= $sold . BOX_LABEL_SOLD ?>
                        </span>
                        </div>
                    </div>
                </li>
            <?php } ?>
        </ul>
        <div class="panel-footer text-center">
            <a href="<?= zen_href_link(FILENAME_STATS_PRODUCTS_PURCHASED) ?>" class="btn btn-default btn-xs btn-block"><?= BOX_LABEL_VIEW_FULL_REPORT ?></a>
        </div>
    <?php } else { ?>
        <div class="panel-body text-center text-muted">
            <br><i class="fa fa-frown-o fa-2x"></i><br><br><?= BOX_LABEL_NO_SALES ?>
        </div>
    <?php } ?>
</div>
