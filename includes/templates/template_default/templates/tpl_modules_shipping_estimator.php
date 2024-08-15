<?php
/**
 * Module Template - for shipping-estimator display
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2024 May 31 Modified in v2.1.0-alpha1 $
 */
if ($_SESSION['cart']->count_contents() === 0) {
    return;
}

if (empty($extra)) {
    $extra = '';
} else {
    $extra = ' class="' . $extra . '"';
}
?>
<div id="shippingEstimatorContent">
    <?= zen_draw_form('estimator', zen_href_link($show_in . '#seView', '', $request_type), 'post') ?>
<?php
if (is_array($selected_shipping)) {
    echo zen_draw_hidden_field('scid', $selected_shipping['id']);
}
echo zen_draw_hidden_field('action', 'submit');
?>
    <h2><?= CART_SHIPPING_OPTIONS ?></h2>
<?php
if (!empty($totalsDisplay)) {
?>
    <div class="cartTotalsDisplay important"><?= $totalsDisplay ?></div>
<?php
}

if (zen_is_logged_in() && !zen_in_guest_checkout()) {
    // only display addresses if more than 1
    if ($addresses->RecordCount() > 1) {
?>
    <label class="inputLabel" for="seAddressPulldown"><?= CART_SHIPPING_METHOD_ADDRESS ?></label>
    <?= zen_draw_pull_down_menu('address_id', $addresses_array, $selected_address, 'onchange="return shipincart_submit();" id="seAddressPulldown"') ?>
<?php
    }
?>
    <div class="bold back" id="seShipTo"><?= CART_SHIPPING_METHOD_TO ?></div>
    <address class="back">
        <?= zen_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>') ?>
    </address>
    <br class="clearBoth">
<?php
} else {
    if ($_SESSION['cart']->get_content_type() !== 'virtual') {
        $flag_show_pulldown_states = (ACCOUNT_STATE_DRAW_INITIAL_DROPDOWN === 'true');
?>
    <label class="inputLabel" for="country"><?= ENTRY_COUNTRY ?></label>
    <?= zen_get_country_list('zone_country_id', $selected_country, 'id="country"' . (($flag_show_pulldown_states) ? ' onchange="update_zone(this.form);"' : '')) ?>
    <br class="clearBoth">

    <a id="seView"></a>
    <label class="inputLabel" for="stateZone" id="zoneLabel"><?= ENTRY_STATE ?></label>
<?php
        if ($flag_show_pulldown_states) {
?>
    <?= zen_draw_pull_down_menu('zone_id', zen_prepare_country_zones_pull_down($selected_country), $state_zone_id, 'id="stateZone"') ?>
    <br class="clearBoth" id="stBreak">
<?php
        }
?>
    <label class="inputLabel" for="state" id="stateLabel"><?= ($state_field_label ?? '') ?></label>
    <?= zen_draw_input_field('state', $selectedState, zen_set_field_length(TABLE_ADDRESS_BOOK, 'entry_state', '40') . ' id="state"') .'&nbsp;<span class="alert" id="stText">&nbsp;</span>' ?>
    <br class="clearBoth">
<?php
        if (CART_SHIPPING_METHOD_ZIP_REQUIRED === 'true') {
?>
    <label class="inputLabel" for="postcode"><?= ENTRY_POST_CODE ?></label>
    <?= zen_draw_input_field('postcode', $postcode, 'size="7" id="postcode"') ?>
    <br class="clearBoth">
<?php
        }
?>
    <div class="buttonRow forward"><?= zen_image_submit(BUTTON_IMAGE_UPDATE, BUTTON_UPDATE_ALT) ?></div>
    <br class="clearBoth">
<?php
    }
}

if ($_SESSION['cart']->get_content_type() === 'virtual') {
    echo CART_SHIPPING_METHOD_FREE_TEXT . ' ' . CART_SHIPPING_METHOD_ALL_DOWNLOADS;
} elseif ($free_shipping == 1) {
    echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER));
} else {
?>
    <table id="seQuoteResults">
<?php
    if (!zen_is_logged_in() || zen_in_guest_checkout()) {
?>
        <tr>
            <td colspan="2" class="seDisplayedAddressLabel">
                <?= CART_SHIPPING_QUOTE_CRITERIA ?><br>
                <?= '<span class="seDisplayedAddressInfo">' .
                    zen_get_zone_name((int)$selected_country, (int)$state_zone_id, '') .
                    ($selectedState != '' ? ' ' . $selectedState : '') . ' ' .
                    ($order->delivery['postcode'] ?? '') . ' ' .
                    zen_get_country_name($order->delivery['country_id']) .
                '</span>' ?>
            </td>
        </tr>
<?php
    }
?>
        <tr>
            <th scope="col" id="seProductsHeading"><?= CART_SHIPPING_METHOD_TEXT ?></th>
            <th scope="col" id="seTotalHeading"><?= CART_SHIPPING_METHOD_RATES ?></th>
        </tr>
<?php
    foreach ($quotes as $next_module) {
        $thisquoteid = '';
        if (empty($next_module['module'])) {
            continue;
        }

        if (isset($next_module['id']) && count($next_module['methods']) === 1 && isset($next_module['methods'][0]['id'])) {
            // simple shipping method
            $thisquoteid = $next_module['id'] . '_' . $next_module['methods'][0]['id'];
?>
        <tr<?= $extra ?>>
<?php
            if (isset($next_module['error']) && $next_module['error']) {
?>
            <td colspan="2"><?= $next_module['module'] ?>&nbsp;(<?= $next_module['error'] ?>)</td>
<?php
            } elseif ($selected_shipping['id'] === $thisquoteid) {
?>
            <td class="bold"><?= $next_module['module'] ?>&nbsp;(<?= $next_module['methods'][0]['title'] ?>)</td>
            <td class="cartTotalDisplay bold"><?= $currencies->format(zen_add_tax($next_module['methods'][0]['cost'], $next_module['tax'] ?? 0)) ?></td>
<?php
            } else {
?>
            <td><?= $next_module['module'] ?>&nbsp;(<?= $next_module['methods'][0]['title'] ?>)</td>
            <td class="cartTotalDisplay"><?= $currencies->format(zen_add_tax($next_module['methods'][0]['cost'], $next_module['tax'] ?? 0)) ?></td>
<?php
            }
?>
        </tr>
<?php
        } elseif (empty($next_module['methods']) || !is_array($next_module['methods'])) {
            continue;
        } else {
            // shipping method with sub methods (multipickup) or none
            foreach ($next_module['methods'] as $next_method) {
                $thisquoteid = '';
                if (isset($next_module['id']) && isset($next_method['id'])) {
                    $thisquoteid = $next_module['id'] . '_' . $next_method['id'];
                }
?>
        <tr<?= $extra ?>>
<?php
                if (!empty($next_module['error'])){
?>
            <td colspan="2"><?= $next_module['module'] ?>&nbsp;(<?= $next_module['error'] ?>)</td>
<?php
                } elseif ($selected_shipping['id'] === $thisquoteid){
?>
            <td class="bold"><?= $next_module['module'] ?>&nbsp;(<?= $next_method['title'] ?>)</td>
            <td class="cartTotalDisplay bold">
                <?= $currencies->format(zen_add_tax($next_method['cost'], $next_module['tax'] ?? 0)) ?>
            </td>
<?php
                } else {
?>
            <td><?= $next_module['module'] ?>&nbsp;(<?= $next_method['title'] ?>)</td>
            <td class="cartTotalDisplay">
                <?= $currencies->format(zen_add_tax($next_method['cost'], $next_module['tax'] ?? 0)) ?>
            </td>
<?php
                }
?>
       </tr>
<?php
            }
        }
    }
?>
    </table>
<?php
    if (empty($quotes)) {
?>
        <div id="noShippingAvailable" class="alert important">
            <?= TEXT_NO_SHIPPING_AVAILABLE_ESTIMATOR ?>
        </div>
<?php
    }
}
?>
    <?= '</form>' ?>
</div>
