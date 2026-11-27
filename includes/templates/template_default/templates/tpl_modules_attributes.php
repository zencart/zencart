<?php

declare(strict_types=1);
/**
 * Module Template
 *
 * Template used to render attribute display/input fields
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */

?>
<div id="productAttributes">
    <?php
    // show "Please Select" unless all are readonly
    if ($zv_display_select_option > 0) { ?>
        <h3 id="attribsOptionsText"><?= TEXT_PRODUCT_OPTIONS ?></h3>
    <?php
    }

    for ($i = 0, $j = count($options_name); $i < $j; $i++) {
        ?>
        <div class="attribBlock">
            <?php
            if ($options_comment[$i] != '' && $options_comment_position[$i] === '0') {
                ?>
                <h3 class="attributesComments"><?= $options_comment[$i] ?></h3>
                <?php
            }
            ?>
            <div class="wrapperAttribsOptions" id="<?= $options_html_id[$i] ?>">
                <h4 class="optionName back"><?= $options_name[$i] ?></h4>
                <div class="back"><?= $options_menu[$i] ?></div>
                <br class="clearBoth">
            </div>

            <?php
            if ($options_comment[$i] != '' && $options_comment_position[$i] === '1') { ?>
                <div class="ProductInfoComments"><?= $options_comment[$i] ?></div>
            <?php
            }

            if (!empty($options_attributes_image[$i])) {
                echo $options_attributes_image[$i];
            }
            ?>
            <br class="clearBoth">
        </div>
        <?php
    }

    if ($show_onetime_charges_description) {
        ?>
        <div class="wrapperAttribsOneTime"><?= TEXT_ONETIME_CHARGE_SYMBOL . TEXT_ONETIME_CHARGE_DESCRIPTION ?></div>
    <?php
    }

    if ($show_attributes_qty_prices_description) {
        ?>
        <div class="wrapperAttribsQtyPrices">
            <?= zen_image(DIR_WS_TEMPLATE_ICONS . 'icon_status_green.gif', TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK, 10, 10) . '&nbsp;' .
                '<a href="javascript:popupWindowPrice(\'' . zen_href_link(
                    FILENAME_POPUP_ATTRIBUTES_QTY_PRICES,
                    'products_id=' . $_GET['products_id'] . '&products_tax_class_id=' . $products_tax_class_id
                ) . '\')">' . TEXT_ATTRIBUTES_QTY_PRICE_HELP_LINK . '</a>' ?>
        </div>
    <?php
    } ?>
</div>
