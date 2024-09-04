<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Aug 26 Modified in v2.1.0-alpha2 $
 */
// -----
// Determine which blocks are to be displayed; they're currently set by
// init_includes/init_non_db_settings.php.
//
$display_payment = in_array(ORDER_STATUS_DISPLAY_PAYMENT, ['true', true]);
$display_shipping = in_array(ORDER_STATUS_DISPLAY_SHIPPING, ['true', true]);
$display_products = in_array(ORDER_STATUS_DISPLAY_PRODUCTS, ['true', true]);
?>
<div class="centerColumn" id="orderStatus">
    <h1 id="orderHistoryHeading"><?= HEADING_TITLE ?></h1>
<?php
if ($messageStack->size('order_status') > 0) {
    echo $messageStack->output('order_status');
}

if (isset($order)) { 
?>
    <fieldset>
        <h2 id="orderHistoryDetailedOrder"><?= SUB_HEADING_TITLE . ORDER_HEADING_DIVIDER . sprintf(HEADING_ORDER_NUMBER, $_POST['order_id']) ?></h2>
        <div class="forward"><?= HEADING_ORDER_DATE . ' ' . zen_date_long($order->info['date_purchased']) ?></div>
<?php
    if ($display_products === true) {
?>
        <table id="orderHistoryHeading">
            <tr class="tableHeading">
                <th scope="col" id="myAccountQuantity"><?= HEADING_QUANTITY ?></th>
                <th scope="col" id="myAccountProducts"><?= HEADING_PRODUCTS ?></th>
<?php
        if (count($order->info['tax_groups']) > 1) {
?>
                <th scope="col" id="myAccountTax"><?= HEADING_TAX ?></th>
<?php
        }
?>
                <th scope="col" id="myAccountTotal"><?= HEADING_TOTAL ?></th>
            </tr>
<?php
        $currency = $order->info['currency'];
        $currency_value = $order->info['currency_value'];
        foreach ($order->products as $current_product) {
?>
            <tr>
                <td class="accountQuantityDisplay"><?= $current_product['qty'] . QUANTITY_SUFFIX ?></td>
                <td class="accountProductDisplay"><?= $current_product['name'];

            if (isset($current_product['attributes']) && is_array($current_product['attributes']) && count($current_product['attributes']) > 0) {
?>
                    <ul id="orderAttribsList">
<?php
                foreach ($current_product['attributes'] as $current_attribute) {
?>
                        <li><?= $current_attribute['option'] . TEXT_OPTION_DIVIDER . nl2br(zen_output_string_protected($current_attribute['value']), false) ?></li>
<?php
                }
?>
                    </ul>
<?php
            }
?>
                </td>
<?php
            $product_tax = $current_product['tax'];
            if (count($order->info['tax_groups']) > 1) {
?>
                <td class="accountTaxDisplay"><?= zen_display_tax_value($product_tax) . '%' ?></td>
<?php
            }
?>
                <td class="accountTotalDisplay">
                    <?= $currencies->format(zen_add_tax($current_product['final_price'], $product_tax) * $current_product['qty'], true, $currency, $currency_value) . ($current_product['onetime_charges'] != 0 ? '<br>' . $currencies->format(zen_add_tax($current_product['onetime_charges'], $product_tax), true, $currency, $currency_value) : '') ?></td>
            </tr>
<?php
        }
?>
        </table>
        <hr>
        <div id="orderTotals">
<?php
        foreach ($order->totals as $current_ot) {
?>
            <div class="amount larger forward"><?= $current_ot['text'] ?></div>
            <div class="lineTitle larger forward"><?= $current_ot['title'] ?></div>
            <div class="clearBoth"></div>
<?php
        }
?>
        </div>
<?php
    }

    // -----
    // Displays any downloads associated with the order.  The base processing (from the zc156 version) will
    // search based on an email address, if set into the session.
    //
    // We'll set the order's email address into the session for that module's processing and then remove
    // that value, once finished.
    //
    if (DOWNLOAD_ENABLED === 'true') {
        require $template->get_template_dir('tpl_modules_downloads.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_downloads.php';
    }
    
    // -----
    // Display the order's status information.
    //
    if (!empty($statusArray)) {
?>
        <table id="myAccountOrdersStatus">
            <caption><h2 id="orderHistoryStatus"><?= HEADING_ORDER_HISTORY ?></h2></caption>
            <tr class="tableHeading">
                <th scope="col" id="myAccountStatusDate"><?= TABLE_HEADING_STATUS_DATE ?></th>
                <th scope="col" id="myAccountStatus"><?= TABLE_HEADING_STATUS_ORDER_STATUS ?></th>
                <th scope="col" id="myAccountStatusComments"><?= TABLE_HEADING_STATUS_COMMENTS ?></th>
            </tr>
<?php
        // -----
        // Only the **first** order comment -- the one provided by the customer -- is "protected", i.e. any HTML tags
        // display as the tag itself without the HTML being formatted.  All others have been provided by an
        // admin or a trusted 3rd-party (like a payment method) and are trusted not to have 'naughty' HTML.
        //
        $protected = true;
        foreach ($statusArray as $statuses) {
?>
            <tr>
                <td><?= zen_date_short($statuses['date_added']) ?></td>
                <td><?= $statuses['orders_status_name'] ?></td>
                <td><?= (empty($statuses['comments']) ? '&nbsp;' : nl2br(zen_output_string($statuses['comments'], false, $protected), false)) ?></td>
            </tr>
<?php
            $protected = false;
        }
?>
        </table>
<?php
    }
?>
        <hr>
<?php
    if ($display_shipping === true) {
?>
        <div id="myAccountShipInfo" class="floatingBox back">
<?php
        if (!empty($order->info['shipping_method'])) {
?>
            <h4><?= HEADING_SHIPPING_METHOD ?></h4>
            <div><?= $order->info['shipping_method'] ?></div>
<?php 
        } else { // temporary just remove these 4 lines ?>
            <div>WARNING: Missing Shipping Information</div>
<?php
        }
?>
        </div>
<?php
    }

    if ($display_products === true) {
?>
        <div id="myAccountPaymentInfo" class="floatingBox forward">
            <h4><?= HEADING_PAYMENT_METHOD ?></h4>
            <div><?= $order->info['payment_method'] ?></div>
        </div>
<?php
    }
?>
        <div class="clearBoth"></div>
    </fieldset>
<?php
}

echo zen_draw_form('order_status', zen_href_link(FILENAME_ORDER_STATUS, 'action=status', $request_type), 'post');
?>
    <fieldset>
        <legend><?= HEADING_TITLE ?></legend>
        <p><?= TEXT_LOOKUP_INSTRUCTIONS ?></p>

        <label class="inputLabel"><?= ENTRY_ORDER_NUMBER ?></label>
        <?= zen_draw_input_field('order_id', $orderID, 'size="10" id="order_id" required', 'number') ?> 
        <br>

        <label class="inputLabel"><?= ENTRY_EMAIL ?></label>
        <?= zen_draw_input_field('query_email_address', $query_email_address, 'size="35" id="query_email_address" required', 'email') ?> 
        <br>

        <?= zen_draw_input_field($spam_input_name, '', ' size="40" id="CUAS" style="visibility:hidden; display:none;" autocomplete="off"') ?>
        <?= $extra_validation_html ?>

        <div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_CONTINUE, BUTTON_CONTINUE_ALT) ?></div>
    </fieldset></form>
</div>
