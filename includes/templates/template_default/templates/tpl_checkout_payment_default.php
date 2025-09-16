<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=checkout_payment.
 * Displays the allowed payment modules, for selection by customer.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2023 Oct 05 Modified in v2.0.0-alpha1 $
 */

echo $payment_modules->javascript_validation(); ?>
<div class="centerColumn" id="checkoutPayment">
    <?php
    echo zen_draw_form('checkout_payment', zen_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post');
    echo zen_draw_hidden_field('action', 'submit');
    ?>

    <h1 id="checkoutPaymentHeading"><?= HEADING_TITLE ?></h1>

    <?php
    if ($messageStack->size('redemptions') > 0) {
        echo $messageStack->output('redemptions');
    }
    if ($messageStack->size('checkout') > 0) {
        echo $messageStack->output('checkout');
    }
    if ($messageStack->size('checkout_payment') > 0) {
        echo $messageStack->output('checkout_payment');
    }

    // ** BEGIN PAYPAL EXPRESS CHECKOUT **
    if (!$payment_modules->in_special_checkout()) {
        // ** END PAYPAL EXPRESS CHECKOUT ** ?>
        <h2 id="checkoutPaymentHeadingAddress"><?= TITLE_BILLING_ADDRESS ?></h2>

        <div id="checkoutBillto" class="floatingBox back">
            <?php
            if (MAX_ADDRESS_BOOK_ENTRIES >= 2) { ?>
                <div class="buttonRow forward"><a href="<?= zen_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') ?>"><?= zen_image_button(BUTTON_IMAGE_CHANGE_ADDRESS, BUTTON_CHANGE_ADDRESS_ALT) ?></a></div>
            <?php
            } ?>
            <address><?= zen_address_label($_SESSION['customer_id'], $_SESSION['billto'], true, ' ', '<br>') ?></address>
        </div>

        <div class="floatingBox important forward"><?= TEXT_SELECTED_BILLING_DESTINATION ?></div>
        <br class="clearBoth">
        <br>
        <?php
        // ** BEGIN PAYPAL EXPRESS CHECKOUT **
    }
    // ** END PAYPAL EXPRESS CHECKOUT ** ?>

    <fieldset id="checkoutOrderTotals">
        <legend id="checkoutPaymentHeadingTotal"><?= TEXT_YOUR_TOTAL ?></legend>
        <?php
        if (MODULE_ORDER_TOTAL_INSTALLED) {
            $order_totals = $order_total_modules->process();
            $order_total_modules->output();
        }
        ?>
    </fieldset>

    <?php
    $selection = $order_total_modules->credit_selection();
    if (sizeof($selection) > 0) {
        for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
            if (isset($_GET['credit_class_error_code']) && ($_GET['credit_class_error_code'] == (isset($selection[$i]['id'])) ? $selection[$i]['id'] : 0)) {
                ?>
                <div class="messageStackError"><?= zen_output_string_protected($_GET['credit_class_error']) ?></div>
                <?php
            }
            for ($j = 0, $n2 = (isset($selection[$i]['fields']) ? sizeof($selection[$i]['fields']) : 0); $j < $n2; $j++) {
                ?>
                <fieldset>
                    <legend><?= $selection[$i]['module'] ?></legend>
                    <?= $selection[$i]['redeem_instructions'] ?>
                    <div class="gvBal larger"><?= $selection[$i]['checkbox'] ?? '' ?></div>
                    <label class="inputLabel"<?= $selection[$i]['fields'][$j]['tag'] ? ' for="' . $selection[$i]['fields'][$j]['tag'] . '"' : '' ?>><?= $selection[$i]['fields'][$j]['title'] ?></label>
                    <?= $selection[$i]['fields'][$j]['field'] ?>
                </fieldset>
                <?php
            }
        }
    }

    // ** BEGIN PAYPAL EXPRESS CHECKOUT **
    if (!$payment_modules->in_special_checkout()) {
        // ** END PAYPAL EXPRESS CHECKOUT ** ?>
        <fieldset class="payment">
            <legend><?= HEADING_PAYMENT_METHOD ?></legend>
            <?php
            if (SHOW_ACCEPTED_CREDIT_CARDS != '0') {
                if (SHOW_ACCEPTED_CREDIT_CARDS == '1') {
                    echo TEXT_ACCEPTED_CREDIT_CARDS . zen_get_cc_enabled();
                }
                if (SHOW_ACCEPTED_CREDIT_CARDS == '2') {
                    echo TEXT_ACCEPTED_CREDIT_CARDS . zen_get_cc_enabled('IMAGE_');
                }
                ?>
                <br class="clearBoth">
            <?php
            }

            $selection = $payment_modules->selection();
            if (sizeof($selection) > 1) {
                ?>
                <p class="important"><?= TEXT_SELECT_PAYMENT_METHOD ?></p>
                <?php
            } elseif (sizeof($selection) == 0) {
                ?>
                <p class="important"><?= TEXT_NO_PAYMENT_OPTIONS_AVAILABLE ?></p>
                <?php
            }

            $radio_buttons = 0;
            for ($i = 0, $n = sizeof($selection); $i < $n; $i++) {
                if (sizeof($selection) > 1) {
                    if (empty($selection[$i]['noradio'])) {
                        echo zen_draw_radio_field('payment', $selection[$i]['id'], ($selection[$i]['id'] == ($_SESSION['payment'] ?? '')), 'id="pmt-' . $selection[$i]['id'] . '"');
                    }
                } else {
                    echo zen_draw_hidden_field('payment', $selection[$i]['id'], 'id="pmt-' . $selection[$i]['id'] . '"');
                }
                ?>
                <label for="pmt-<?= $selection[$i]['id'] ?>" class="radioButtonLabel"><?= $selection[$i]['module'] ?></label>
                <?php
                if (defined('MODULE_ORDER_TOTAL_COD_STATUS') && MODULE_ORDER_TOTAL_COD_STATUS == 'true' && $selection[$i]['id'] == 'cod') {
                    ?>
                    <div class="alert"><?= TEXT_INFO_COD_FEES ?></div>
                    <?php
                } else {
                    // echo 'WRONG ' . $selection[$i]['id'];
                }
                ?>
                <?php
                if (!empty(($selection[$i]['text']))) { ?>
                    <div class="ccinfoText"><?= $selection[$i]['text'] ?></div>
                    <?php
                }
                ?>
                <br class="clearBoth">

                <?php
                if (isset($selection[$i]['error'])) {
                    ?>
                    <div><?= $selection[$i]['error'] ?></div>

                    <?php
                } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
                    ?>

                    <div class="ccinfo">
                        <?php
                        for ($j = 0, $n2 = sizeof($selection[$i]['fields']); $j < $n2; $j++) {
                            ?>
                            <label<?= isset($selection[$i]['fields'][$j]['tag']) ? ' for="' . $selection[$i]['fields'][$j]['tag'] . '"' : '' ?> class="inputLabelPayment"><?= $selection[$i]['fields'][$j]['title'] ?></label>
                            <?= $selection[$i]['fields'][$j]['field'] ?>
                            <br class="clearBoth">
                            <?php
                        }
                        ?>
                    </div>
                    <br class="clearBoth">
                    <?php
                }
                $radio_buttons++;
                ?>
                <br class="clearBoth">
                <?php
            }
            ?>

        </fieldset>
        <?php
        // ** BEGIN PAYPAL EXPRESS CHECKOUT **
    } else {
        ?>
        <input type="hidden" name="payment" value="<?= $_SESSION['payment'] ?>">
        <?php
    }
    // ** END PAYPAL EXPRESS CHECKOUT ** ?>
    <fieldset>
        <legend><?= HEADING_ORDER_COMMENTS ?></legend>
        <?= zen_draw_textarea_field('comments', '45', '3', ($comments ?? ''), 'aria-label="' . HEADING_ORDER_COMMENTS . '"') ?>
    </fieldset>

    <?php
    if (DISPLAY_CONDITIONS_ON_CHECKOUT === 'true') {
        ?>
        <fieldset>
            <legend><?= TABLE_HEADING_CONDITIONS ?></legend>
            <div><?= TEXT_CONDITIONS_DESCRIPTION ?></div>
            <?= zen_draw_checkbox_field('conditions', '1', (isset($_SESSION['conditions']) && $_SESSION['conditions'] === '1'), 'id="conditions" required oninput="this.setCustomValidity(\'\')" oninvalid="this.setCustomValidity(\'' . ERROR_CONDITIONS_NOT_ACCEPTED . '\')"') ?>
            <label class="checkboxLabel" for="conditions"><?= TEXT_CONDITIONS_CONFIRM ?></label>
        </fieldset>
        <?php
    }
    ?>
    <div class="buttonRow forward" id="paymentSubmit"><?= zen_image_submit(BUTTON_IMAGE_CONTINUE_CHECKOUT, BUTTON_CONTINUE_ALT, 'onclick="submitFunction(' . $gv_balance . ',' . $order->info['total'] . ')"') ?></div>
    <div class="buttonRow back"><strong><?= TITLE_CONTINUE_CHECKOUT_PROCEDURE ?></strong><br><?= TEXT_CONTINUE_CHECKOUT_PROCEDURE ?></div>
    <?= '</form>' ?>
</div>
