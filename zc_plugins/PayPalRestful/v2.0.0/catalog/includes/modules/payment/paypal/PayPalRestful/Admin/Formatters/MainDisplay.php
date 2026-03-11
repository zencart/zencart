<?php
/**
 * A class that provides the main PayPal request history table for a given order
 * in the Zen Cart admin placed with the PayPal Restful payment module.
 *
 * @copyright Copyright 2023-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Aug 2025 $
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Admin\Formatters;

use PayPalRestful\Common\Helpers;
use PayPalRestful\Zc2Pp\Amount;

class MainDisplay
{
    protected $mainDisplay = '';

    protected $settledFunds = [
        'currency' => '',
        'value' => 0,
        'fee' => 0,
        'exchange_rate' => 0,
    ];

    protected $modals = '';

    protected $amount;

    protected $currencyCode;

    protected $paypalDbTxns;

    protected $jQueryLoadRequired = false;

    protected static $txnTableFields = [
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_TYPE, 'field' => 'txn_type', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_PARENT_TXN_ID, 'field' => 'txn_id', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_DATE_CREATED, 'field' => 'date_added', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_PAYMENT_TYPE, 'field' => 'payment_type'],
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_STATUS, 'field' => 'payment_status'],
        ['name' => MODULE_PAYMENT_PAYPALR_NAME_EMAIL_ID, 'field' => 'payer_email'],
        ['name' => MODULE_PAYMENT_PAYPALR_GROSS_AMOUNT, 'field' => 'mc_gross', 'align' => 'right', 'is_amount' => true],
        ['name' => MODULE_PAYMENT_PAYPALR_PAYMENT_FEE, 'field' => 'payment_fee', 'align' => 'right', 'is_amount' => true],
    ];

    protected static $paymentTableFields = [
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_TYPE, 'field' => 'txn_type', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_ID, 'field' => 'txn_id', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_DATE_CREATED, 'field' => 'date_added', 'align' => 'center'],
        ['name' => MODULE_PAYMENT_PAYPALR_TXN_STATUS, 'field' => 'payment_status'],
        ['name' => MODULE_PAYMENT_PAYPALR_EXCHANGE_RATE, 'field' => 'exchange_rate', 'align' => 'right'],
        ['name' => MODULE_PAYMENT_PAYPALR_GROSS_AMOUNT, 'field' => 'payment_gross', 'align' => 'right', 'is_amount' => true],
        ['name' => MODULE_PAYMENT_PAYPALR_PAYMENT_FEE, 'field' => 'payment_fee', 'align' => 'right', 'is_amount' => true],
        ['name' => MODULE_PAYMENT_PAYPALR_SETTLE_AMOUNT, 'field' => 'settle_amount', 'align' => 'right', 'is_amount' => true],
    ];

    public function __construct(array $paypal_db_txns)
    {
        $this->paypalDbTxns = $paypal_db_txns;

        $this->currencyCode = $paypal_db_txns[0]['mc_currency'];
        $this->amount = new Amount($this->currencyCode);

        $this->mainDisplay =
            '<style>' . file_get_contents(DIR_FS_CATALOG . DIR_WS_INCLUDES . 'modules/payment/paypal/PayPalRestful/paypalr.admin.css') . '</style>' .
            $this->buildTxnTable() .
            $this->buildPaymentsTable();

        // -----
        // Done here instead of in the concatenation above, since the modals element is
        // created during the buildTxnTable call!
        //
        $this->mainDisplay .=
            $this->modals .
            $this->loadJQuery();
    }

    public function get(): string
    {
        return $this->mainDisplay;
    }

    protected function buildTxnTable(): string
    {
        $include_action_column = true;
        return
            "<table class=\"table ppr-table\">\n" .
            '  <caption class="lead text-center">' . MODULE_PAYMENT_PAYPALR_TXN_TABLE_CAPTION . "</caption>\n" .
            "  <tbody>\n" .
                $this->buildTableHeader(self::$txnTableFields, $include_action_column) .
                $this->buildTxnTableData() .
            "  </tbody>\n" .
            "</table>\n";
    }

    protected function buildPaymentsTable(): string
    {
        $include_action_column = false;
        return
            "<table class=\"table ppr-table\">\n" .
            '  <caption class="lead text-center">' .
                '<span id="pt-caption">' . MODULE_PAYMENT_PAYPALR_PAYMENTS_TABLE_CAPTION . '</span>' .
                '<small><sup>1</sup>' . MODULE_PAYMENT_PAYPALR_PAYMENTS_TABLE_NOTE . '</sup></small>' .
            "</caption>\n" .
            "  <tbody>\n" .
                $this->buildTableHeader(self::$paymentTableFields, $include_action_column) .
                $this->buildPaymentTableData() .
            "  </tbody>\n" .
            "</table><hr>\n";
    }

    protected function buildTableHeader(array $table_fields, bool $include_action_column): string
    {
        $header =
            "<tr class=\"dataTableHeadingRow\">\n";

        foreach ($table_fields as $next_field) {
            $align_class = (isset($next_field['align'])) ? " text-{$next_field['align']}" : '';
            $header .=
                "  <th class=\"dataTableHeadingContent$align_class\">" . rtrim($next_field['name'], ':') . "</th>\n";
        }

        if ($include_action_column === true) {
            $header .=
                '  <th class="dataTableHeadingContent text-right">' . MODULE_PAYMENT_PAYPALR_ACTION . "</th>\n";
                "</tr>\n";
        }

        return $header;
    }

    protected function buildTxnTableData(): string
    {
        $found_captures = false;
        $capture_indices = [];
        $last_auth_index = null;
        $found_refunds = false;
        $refund_indices = [];
        $transaction_voided = false;
        $main_txn_id = '';

        $data = '';
        $txn_index = -1;
        foreach ($this->paypalDbTxns as $next_txn) {
            $action_buttons = '';
            $modals = '';

            $data .=
                "<tr class=\"dataTableRow\">\n";

            $txn_index++;
            foreach (self::$txnTableFields as $next_field) {
                // -----
                // Retrieve the field's value, converting it to an 'amount' if so indicated.
                //
                $value = $next_txn[$next_field['field']];
                if (isset($next_field['is_amount']) && $value !== null) {
                    $value = $this->amount->getValueFromString($value);
                }

                // -----
                // Special cases where fields are combined to reduce columns required.
                //
                switch ($next_field['field']) {
                    // -----
                    // Special case for 'txn_id' field, it's preceeded by its parent-txn-id.
                    //
                    case 'txn_id':
                        $value =
                            ((empty($next_txn['parent_txn_id'])) ? '&mdash;' : $next_txn['parent_txn_id']) .
                            '<br>' .
                            $value;
                        break;

                    // -----
                    // Special case for 'payer_email' field, it's displayed as the first/last name,
                    // email-address and payer-id in a single column.
                    //
                    case 'payer_email':
                        $first_name = $next_txn['first_name'];
                        $last_name = $next_txn['last_name'];
                        $payment_type = $next_txn['payment_type'];
                        $payer_email = $value;
                        if (($first_name . $last_name) !== '') {
                            $value = $first_name . ' ' . $last_name;
                            if ($payment_type === 'paypal') {
                                $value .= ' (' . $next_txn['payer_status'] . ')<br>' . $payer_email;
                            }
                        }
                        if ($payment_type === 'paypal') {
                            $value .= '<br>' . $next_txn['payer_id'];
                        }
                        break;

                    // -----
                    // Special case for 'payment_status' field, it's followed by its "pending_reason",
                    // if present.
                    //
                    case 'payment_status':
                        if ($next_txn['pending_reason'] !== null) {
                            $value .= '<br><small>' . $next_txn['pending_reason'] . '</small>';
                        }
                        break;

                    // -----
                    // Special case for 'mc_gross' and 'payment_fee' fields, they're followed by its "mc_currency",
                    // if present.
                    //
                    case 'mc_gross':
                    case 'payment_fee':
                        if ($value === null) {
                            $value = '&mdash;';
                        } else {
                            $value .= ' ' . $next_txn['mc_currency'];
                        }
                        break;

                    default:
                        if (empty($value) || $value === '0001-01-01 00:00:00') {
                            $value = '&mdash;';
                        }
                        break;
                }

                $align_class = (isset($next_field['align'])) ? " text-{$next_field['align']}" : '';
                $data .=
                    "  <td class=\"dataTableContent$align_class\">$value</td>\n";
            }

            // -----
            // Determine possible actions for a PayPal transaction.
            //
            $action_buttons = "::action::$txn_index";
            switch ($next_txn['txn_type']) {
                case 'CREATE':
                    $action_buttons = $this->createActionButton('details', MODULE_PAYMENT_PAYPALR_ACTION_DETAILS, 'primary');
                    $days_to_settle = '';
                    if ($next_txn['expiration_time'] !== null) {
                        $days_to_settle = Helpers::getDaysTo($next_txn['expiration_time']);
                    }
                    $modals = $this->createDetailsModal($next_txn, $days_to_settle);
                    $main_txn_id = $next_txn['txn_id'];
                    break;

                case 'AUTHORIZE':
                    list($action_buttons, $modals) = $this->createAuthButtonsAndModals($txn_index, $main_txn_id, $days_to_settle);
                    break;

                case 'CAPTURE':
                    list($action_buttons, $modals) = $this->createCaptureButtonsAndModals($txn_index);
                    break;

                case 'REFUND':
                    $action_buttons = '';
                    $modals = '';
                    break;

                default:
                    break;
            }

            $this->modals .= $modals;

            $data .=
                '  <td class="dataTableContent text-right">' . (($action_buttons === '') ? '&mdash;' : $action_buttons) . "</td>\n";
                "</tr>\n";
        }

        return $data;
    }

    protected function createActionButton(string $modal_name, string $button_name, string $button_color): string
    {
        return '<button type="button" class="btn btn-' . $button_color . ' btn-sm" data-toggle="modal" data-target="#' . $modal_name . 'Modal">' . $button_name . '</button>';
    }

    protected function createDetailsModal(array $create_fields, string $days_to_settle): string
    {
        // -----
        // The 'memo' field of the PayPal table contains JSON-encoded "interesting bits" from the order's creation.
        //
        $memo = json_decode($create_fields['memo'] ?? '', true);
        $amount_mismatch_panel = '';
        $card_info = [];
        if ($memo !== null) {
            // -----
            // If the amount_mismatch array isn't empty, there was a discrepancy between
            // the order's total calculation by the payment-module and that calculated
            // by the base Zen Cart handling.
            //
            if (!empty($memo['amount_mismatch'])) {
                $amount_mismatch_panel = $this->createAmountMismatchPanel($memo['amount_mismatch']);
            }

            // -----
            // Grab the 'card_info' element, if present, for the credit-card payment display.
            //
            if (!empty($memo['card_info'])) {
                $card_info = $memo['card_info'];
            }
        }

        $modal_body =
            '<div class="row">' .
                $amount_mismatch_panel . '
                <div class="col-md-6 ppr-pr-0">
                    <h5>' . MODULE_PAYMENT_PAYPALR_BUYER_INFO . '</h5>
                    <div class="form-horizontal">' .
                        $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_PAYER_NAME, $create_fields['first_name'] . ' ' . $create_fields['last_name']);

        if ($create_fields['payment_type'] === 'paypal') {
            $modal_body .=
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_PAYER_ID, $create_fields['payer_id']) .
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_PAYER_EMAIL, $create_fields['payer_email']) .
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_PAYER_STATUS, $create_fields['payer_status']);
        } elseif (count($card_info) !== 0) {
            $modal_body .=
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_CC_TYPE, $card_info['brand']) .
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_CC_NUMBER, $card_info['last_digits']) .
                $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_CC_EXPIRES, $card_info['expiry']);
        }

        if (!empty($create_fields['address_name'])) {
            $address_elements = [
                'address_name' => MODULE_PAYMENT_PAYPALR_ADDRESS_NAME,
                'address_street' => MODULE_PAYMENT_PAYPALR_ADDRESS_STREET,
                'address_city' => MODULE_PAYMENT_PAYPALR_ADDRESS_CITY,
                'address_state' => MODULE_PAYMENT_PAYPALR_ADDRESS_STATE,
                'address_zip' => MODULE_PAYMENT_PAYPALR_ADDRESS_ZIP,
                'address_country' => MODULE_PAYMENT_PAYPALR_ADDRESS_COUNTRY,
            ];
            foreach ($address_elements as $field_name => $label) {
                $value = $create_fields[$field_name];
                $modal_body .= $this->createStaticFormGroup(3, $label, $value);
            }
        }

        $modal_body .=
                    '</div>
                </div>
                <div class="col-md-6 ppr-pr-0">
                    <h5>' . MODULE_PAYMENT_PAYPALR_SELLER_INFO . '</h5>
                    <div class="form-horizontal">';

        $seller_elements = [
            'invoice' => MODULE_PAYMENT_PAYPALR_INVOICE_NUMBER,
            'business' => MODULE_PAYMENT_PAYPALR_MERCHANT_NAME,
            'receiver_email' => MODULE_PAYMENT_PAYPALR_MERCHANT_EMAIL,
            'receiver_id' => MODULE_PAYMENT_PAYPALR_MERCHANT_ID,
        ];
        foreach ($seller_elements as $field_name => $label) {
            if (!empty($create_fields[$field_name])) {
                $modal_body .= $this->createStaticFormGroup(3, $label, $create_fields[$field_name]);
            }
        }

        if (!empty($memo['seller_protection'])) {
            $modal_body .= $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_SELLER_PROTECTION, $memo['seller_protection']['status']);
        }

        if (!empty($memo['processor_response'])) {
            $processor_response = [
                sprintf(MODULE_PAYMENT_PAYPALR_AVS_CODE, $memo['processor_response']['avs_code']),
                sprintf(MODULE_PAYMENT_PAYPALR_RESPONSE_CODE, $memo['processor_response']['response_code']),
            ];
            if (isset($memo['processor_response']['cvv_code'])) {
                $processor_response[] = sprintf(MODULE_PAYMENT_PAYPALR_CVV_CODE, $memo['processor_response']['cvv_code']);
            }
            $modal_body .= $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_PROCESSOR_RESPONSE, implode(', ', $processor_response));
        }

        if (!empty($memo['authentication_result'])) {
            $auth_result = $memo['authentication_result'];
            $auth_info = [];
            if (isset($auth_result['liability_shift'])) {
                $auth_info[] = sprintf(MODULE_PAYMENT_PAYPALR_LIABILITY, $auth_result['liability_shift']);
            }
            if (isset($auth_result['three_d_secure']['authentication_status'])) {
                $auth_info[] = sprintf(MODULE_PAYMENT_PAYPALR_AUTH_STATUS, $auth_result['three_d_secure']['authentication_status']);
            }
            if (isset($auth_result['three_d_secure']['enrollment_status'])) {
                $auth_info[] = sprintf(MODULE_PAYMENT_PAYPALR_ENROLL_STATUS, $auth_result['three_d_secure']['enrollment_status']);
            }
            if (count($auth_info) !== 0) {
                $modal_body .= $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_AUTH_RESULT, implode(', ', $auth_info));
            }
        }

        $modal_body .= $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_GROSS_AMOUNT, $this->amount->getValueFromString($create_fields['mc_gross']) . ' ' . $create_fields['mc_currency']);
        if ($days_to_settle !== '') {
            $modal_body .= $this->createStaticFormGroup(3, MODULE_PAYMENT_PAYPALR_DAYSTOSETTLE, $days_to_settle);
        }

        $modal_body .=
                    '</div>
                </div>
            </div>';

        $modal_title_type = ($create_fields['payment_type'] === 'paypal') ? MODULE_PAYMENT_PAYPALR_DETAILS_TYPE_PAYPAL : MODULE_PAYMENT_PAYPALR_DETAILS_TYPE_CARD;
        return $this->createModal('details', sprintf(MODULE_PAYMENT_PAYPALR_DETAILS_TITLE, $modal_title_type), $modal_body, 'lg');
    }
    protected function createAmountMismatchPanel(array $amount_mismatch): string
    {
        $order_amount = $amount_mismatch['value'] . ' ' . $amount_mismatch['currency_code'];
        $panel =
            '<div class="panel-group">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <h4 class="panel-title text-center">
                            <a data-toggle="collapse" href="#details-mismatch">' . sprintf(MODULE_PAYMENT_PAYPALR_AMOUNT_MISMATCH, $order_amount) . '</a>
                        </h4>
                    </div>
                    <div id="details-mismatch" class="panel-collapse collapse">
                        <div class="panel-body">';

        $calculated_amount = 0;
        foreach ($amount_mismatch['breakdown'] as $element => $element_value) {
            $calculated_amount += $element_value['value'];
            $panel .= '
                            <div class="row">
                                <div class="col-md-6 text-right">' . $element . ':</div>
                                 <div class="col-md-2 text-right">' . $element_value['value'] . ' ' . $element_value['currency_code'] . '</div>
                            </div>';
        }

        $panel .= '
                            <div class="row">
                                <div class="col-md-6 text-right"><b>' . MODULE_PAYMENT_PAYPALR_CALCULATED_AMOUNT . '</b></div>
                                <div class="col-md-2 text-right">' . $this->amount->getValueFromFloat($calculated_amount) . ' ' . $amount_mismatch['currency_code'] . '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

        return $panel;
    }

    protected function createAuthButtonsAndModals(int $auth_index, string $main_txn_id, string $days_to_settle): array
    {
        $action_buttons = '';
        $modals = '';

        // -----
        // Actions on authorizations are allowed:
        // - ONLY on the original authorization
        // - If that authorization has not been voided
        // - Up to and including the 29th day after the original AUTHORIZE transaction was placed.
        //
        if ($days_to_settle <= 30) {
            $authorization = $this->paypalDbTxns[$auth_index];
            if ($authorization['parent_txn_id'] === $main_txn_id) {
                if ($authorization['payment_status'] !== 'VOIDED') {
                    $action_buttons =
                        $this->createActionButton("reauth-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_REAUTH, 'primary') . ' ' .
                        $this->createActionButton("void-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_VOID, 'danger') . ' ' .
                        $this->createActionButton("capture-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_CAPTURE, 'warning');

                    $modals =
                        $this->createReauthModal($auth_index) .
                        $this->createVoidModal($auth_index) .
                        $this->createCaptureModal($auth_index);
                }
            }
        }

        return [$action_buttons, $modals];
    }
    protected function createReauthModal(int $auth_index): string
    {
        foreach ($this->paypalDbTxns as $next_txn) {
            if ($next_txn['txn_type'] === 'AUTHORIZE') {
                if (!isset($first_authorization)) {
                    $first_authorization = $next_txn;
                    $original_auth_value = $first_authorization['mc_gross'];
                    $amount_authorized = $original_auth_value;
                }
                if ($next_txn['mc_gross'] !== $amount_authorized) {
                    $amount_authorized = $next_txn['mc_gross'];
                }
                $last_authorization = $next_txn;
            }
        }

        $days_to_settle = Helpers::getDaysTo($first_authorization['expiration_time']);

        $currency_decimals = $this->amount->getCurrencyDecimals();
        $multiplier = ($currency_decimals === 0) ? 1 : 100;
        $original_auth_value = $this->amount->getValueFromString($original_auth_value);
        $maximum_auth_value = $this->amount->getValueFromFloat(floor($original_auth_value * 1.15 * $multiplier) / $multiplier);
        $amount_authorized = $this->amount->getValueFromString($amount_authorized);

        $min_and_step = ($this->amount->getCurrencyDecimals() === 0) ? '1' : '.01';
        $amount_input_params = 'type="number" min="' . $min_and_step . '" max="' . $maximum_auth_value . '" step="' . $min_and_step . '"';
        $amount_help_text = sprintf(MODULE_PAYMENT_PAYPALR_AMOUNT_RANGE, $this->currencyCode, $maximum_auth_value);

        $days_since_last_auth = Helpers::getDaysFrom($last_authorization['date_added']);

        $submit_button_id = "ppr-reauth-submit-$auth_index";

        $modal_body =
            zen_draw_form("auth-form-$auth_index", FILENAME_ORDERS, zen_get_all_get_params(['action']) . '&action=doAuth', 'post', 'class="form-horizontal"') .
                zen_draw_hidden_field('doAuthOid', $first_authorization['order_id']) .
                zen_draw_hidden_field('auth_txn_id', $this->paypalDbTxns[$auth_index]['txn_id']) .
                '<p>' . MODULE_PAYMENT_PAYPALR_REAUTH_INSTRUCTIONS . '</p>' .
                '<p><b>' . MODULE_PAYMENT_PAYPALR_NOTES . '</b></p>' .
                '<ol>
                    <li>' . MODULE_PAYMENT_PAYPALR_REAUTH_NOTE1 . '</li>
                    <li>' . MODULE_PAYMENT_PAYPALR_REAUTH_NOTE2 . '</li>
                    <li>' . MODULE_PAYMENT_PAYPALR_REAUTH_NOTE3 . '</li>
                    <li>' . sprintf(MODULE_PAYMENT_PAYPALR_REAUTH_NOTE4, $maximum_auth_value . ' ' . $this->currencyCode) . '</li>
                </ol>' .
                $this->createStaticFormGroup(6, MODULE_PAYMENT_PAYPALR_REAUTH_ORIGINAL, $original_auth_value . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(6, MODULE_PAYMENT_PAYPALR_REAUTH_NEW_AMOUNT, $amount_authorized . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(6, MODULE_PAYMENT_PAYPALR_DAYSTOSETTLE, $days_to_settle) .
                $this->createStaticFormGroup(6, MODULE_PAYMENT_PAYPALR_REAUTH_DAYS_FROM_LAST, $days_since_last_auth);

        if ($days_since_last_auth > 3) {
            $modal_body .=
                $this->createModalInput(6, MODULE_PAYMENT_PAYPALR_AMOUNT, $amount_authorized, "auth-amt-$auth_index", 'ppr-amount', $amount_input_params, $amount_help_text) .
                $this->createModalButtons("ppr-reauth-submit-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_REAUTH, MODULE_PAYMENT_PAYPALR_CONFIRM);
        } else {
            $modal_body .=
                '<div class="form-group">' .
                    '<p class="col-sm-12 text-center form-control-static text-warning">' .
                        '<b>' . MODULE_PAYMENT_PAYPALR_REAUTH_NOT_POSSIBLE . '</b>' .
                    '</p>' .
                '</div>';
        }
        $modal_body .=
            '</form>';

        return $this->createModal("reauth-$auth_index", MODULE_PAYMENT_PAYPALR_REAUTH_TITLE, $modal_body);
    }
    protected function createCaptureModal(int $auth_index): string
    {
        $auth_db_txn = $this->paypalDbTxns[$auth_index];
        $original_auth_value = $auth_db_txn['mc_gross'];
        $amount_authorized = $original_auth_value;

        $previously_captured_value = 0;
        $auth_txn_id = $auth_db_txn['txn_id'];
        foreach ($this->paypalDbTxns as $next_txn) {
            if ($next_txn['txn_type'] === 'CAPTURE' && $next_txn['parent_txn_id'] === $auth_txn_id) {
                $previously_captured_value += $next_txn['payment_gross'];
            } elseif ($next_txn['txn_type'] === 'AUTHORIZE' && $next_txn['mc_gross'] !== $original_auth_value) {
                $amount_authorized = $next_txn['mc_gross'];
            }
        }

        $original_auth_value = $this->amount->getValueFromString($original_auth_value);
        $amount_authorized = $this->amount->getValueFromString($amount_authorized);
        $amount_remaining = $amount_authorized - $previously_captured_value;
        $maximum_capt_value = $this->amount->getValueFromFloat((float)$amount_remaining);

        $min_and_step = ($this->amount->getCurrencyDecimals() === 0) ? '1' : '.01';
        $amount_input_params = 'type="number" min="' . $min_and_step . '" max="' . $maximum_capt_value . '" step="' . $min_and_step . '"';
        $amount_help_text = sprintf(MODULE_PAYMENT_PAYPALR_AMOUNT_RANGE, $this->currencyCode, $maximum_capt_value);

        $modal_body =
            zen_draw_form("capt-form-$auth_index", FILENAME_ORDERS, zen_get_all_get_params(['action']) . '&action=doCapture', 'post', 'class="form-horizontal"') .
                zen_draw_hidden_field('doCaptOid', $auth_db_txn['order_id']) .
                zen_draw_hidden_field('auth_txn_id', $auth_db_txn['txn_id']) .
                '<p>' . MODULE_PAYMENT_PAYPALR_CAPTURE_INSTRUCTIONS . '</p>' .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_REAUTH_ORIGINAL, $original_auth_value . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_REAUTH_NEW_AMOUNT, $amount_authorized . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_CAPTURED_SO_FAR, $this->amount->getValueFromFloat((float)$previously_captured_value) . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_REMAINING_TO_CAPTURE, $maximum_capt_value . ' ' . $this->currencyCode);

        if ($amount_remaining > 0) {
            $modal_body .=
                $this->createModalInput(4, MODULE_PAYMENT_PAYPALR_AMOUNT, $maximum_capt_value, "capt-amt-$auth_index", 'ppr-amount', $amount_input_params, $amount_help_text) .
                $this->createModalCheckbox(4, MODULE_PAYMENT_PAYPALR_CAPTURE_REMAINING, 'ppr-capt-remaining') .
                $this->createModalTextArea(4, MODULE_PAYMENT_PAYPALR_CUSTOMER_NOTE, MODULE_PAYMENT_PAYPALR_CAPTURE_DEFAULT_MESSAGE, "capt-note-$auth_index", 'ppr-capt-note') .
                $this->createModalCheckbox(4, MODULE_PAYMENT_PAYPALR_CAPTURE_FINAL_TEXT, 'ppr-capt-final') .
                $this->createModalButtons("ppr-capt-submit-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_CAPTURE, MODULE_PAYMENT_PAYPALR_CONFIRM);
        } else {
            $modal_body .=
                '<div class="form-group">' .
                    '<p class="col-sm-12 text-center form-control-static text-warning">' .
                        '<b>' . MODULE_PAYMENT_PAYPALR_CAPTURE_NO_REMAINING . '</b>' .
                    '</p>' .
                '</div>';
        }

        $modal_body .=
            '</form>';

        return $this->createModal("capture-$auth_index", MODULE_PAYMENT_PAYPALR_CAPTURE_TITLE, $modal_body);
    }
    protected function createVoidModal(int $auth_index): string
    {
        $auth_db_txn = $this->paypalDbTxns[$auth_index];

        $modal_body =
            zen_draw_form("void-form-$auth_index", FILENAME_ORDERS, zen_get_all_get_params(['action']) . '&action=doVoid', 'post', 'class="form-horizontal"') .
                zen_draw_hidden_field('doVoidOid', $auth_db_txn['order_id']) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_VOID_AUTH_ID, $auth_db_txn['txn_id']) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_AMOUNT, $this->amount->getValueFromString($auth_db_txn['mc_gross']) . ' ' . $this->currencyCode) .
                '<p>' . MODULE_PAYMENT_PAYPALR_VOID_INSTRUCTIONS . '</p>' .
                $this->createModalInput(4, MODULE_PAYMENT_PAYPALR_VOID_AUTH_ID, '', "void-id-$auth_index", 'ppr-void-id', 'type="text" pattern="[A-Za-z0-9]{17}" required') .
                $this->createModalTextArea(4, MODULE_PAYMENT_PAYPALR_CUSTOMER_NOTE, MODULE_PAYMENT_PAYPALR_VOID_DEFAULT_MESSAGE, "void-note-$auth_index", 'ppr-void-note') .
                $this->createModalButtons("ppr-void-submit-$auth_index", MODULE_PAYMENT_PAYPALR_ACTION_VOID, MODULE_PAYMENT_PAYPALR_CONFIRM) .
            '</form>';
        return $this->createModal("void-$auth_index", MODULE_PAYMENT_PAYPALR_VOID_TITLE, $modal_body);
    }

    protected function createCaptureButtonsAndModals(int $capture_index): array
    {
        $capture_db_txn = $this->paypalDbTxns[$capture_index];
        $original_capture_value = $this->amount->getValueFromString($capture_db_txn['mc_gross']);

        $previously_refunded_value = 0;
        $capture_txn_id = $capture_db_txn['txn_id'];
        foreach ($this->paypalDbTxns as $next_txn) {
            if ($next_txn['txn_type'] === 'REFUND' && $next_txn['parent_txn_id'] === $capture_txn_id) {
                $previously_refunded_value += $next_txn['payment_gross'];
            }
        }
        $maximum_refund_value = (float)($original_capture_value - $previously_refunded_value);
        if ($maximum_refund_value <= 0) {
            return ['', ''];
        }

        // -----
        // Captures can be refunded, so long as they haven't been fully refunded.
        //
        return [
            $this->createActionButton("refund-$capture_index", MODULE_PAYMENT_PAYPALR_ACTION_REFUND, 'warning'),
            $this->createRefundModal($capture_index, $this->amount->getValueFromFloat($maximum_refund_value))
        ];
    }
    protected function createRefundModal(int $capture_index, string $maximum_refund_value): string
    {
        $capture_db_txn = $this->paypalDbTxns[$capture_index];
        $original_capture_value = $this->amount->getValueFromString($capture_db_txn['mc_gross']);

        $previously_refunded_value = 0;
        $capture_txn_id = $capture_db_txn['txn_id'];
        foreach ($this->paypalDbTxns as $next_txn) {
            if ($next_txn['txn_type'] === 'REFUND' && $next_txn['parent_txn_id'] === $capture_txn_id) {
                $previously_refunded_value += $next_txn['payment_gross'];
            }
        }

        $maximum_refund_value = $this->amount->getValueFromFloat((float)($original_capture_value - $previously_refunded_value));

        $min_and_step = ($this->amount->getCurrencyDecimals() === 0) ? '1' : '.01';
        $amount_input_params = 'type="number" min="' . $min_and_step . '" max="' . $maximum_refund_value . '" step="' . $min_and_step . '"';
        $amount_help_text = sprintf(MODULE_PAYMENT_PAYPALR_AMOUNT_RANGE, $this->currencyCode, $maximum_refund_value);

        $modal_body =
            zen_draw_form("refund-form-$capture_index", FILENAME_ORDERS, zen_get_all_get_params(['action']) . '&action=doRefund', 'post', 'class="form-horizontal"') .
                zen_draw_hidden_field('doRefundOid', $capture_db_txn['order_id']) .
                zen_draw_hidden_field('capture_txn_id', $capture_db_txn['txn_id']) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_REFUND_CAPTURE_ID, $capture_db_txn['txn_id']) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_AMOUNT, $this->amount->getValueFromString($capture_db_txn['mc_gross']) . ' ' . $this->currencyCode) .
                $this->createStaticFormGroup(4, MODULE_PAYMENT_PAYPALR_REMAINING_TO_REFUND, $maximum_refund_value . ' ' . $this->currencyCode) .
                '<p>' . MODULE_PAYMENT_PAYPALR_REFUND_INSTRUCTIONS . '</p>' .
                '<ol>
                    <li>' . MODULE_PAYMENT_PAYPALR_REFUND_NOTE1 . '</li>
                    <li>' . MODULE_PAYMENT_PAYPALR_REFUND_NOTE2 . '</li>
                    <li>' . MODULE_PAYMENT_PAYPALR_REFUND_NOTE3 . '</li>
                </ol>' .
                $this->createModalInput(4, MODULE_PAYMENT_PAYPALR_REFUND_AMOUNT, $maximum_refund_value, "refund-amt-$capture_index", 'ppr-amount', $amount_input_params, $amount_help_text) .
                $this->createModalCheckbox(4, MODULE_PAYMENT_PAYPALR_REFUND_FULL, 'ppr-refund-full') .
                $this->createModalTextArea(4, MODULE_PAYMENT_PAYPALR_CUSTOMER_NOTE, MODULE_PAYMENT_PAYPALR_REFUND_DEFAULT_MESSAGE, "refund-note-$capture_index", 'ppr-refund-note') .

                $this->createModalButtons("ppr-refund-submit-$capture_index", MODULE_PAYMENT_PAYPALR_ACTION_REFUND, MODULE_PAYMENT_PAYPALR_CONFIRM) .
            '</form>';
        return $this->createModal("refund-$capture_index", MODULE_PAYMENT_PAYPALR_REFUND_TITLE, $modal_body);
    }

    protected function createStaticFormGroup(int $label_width, string $label_text, string $value_text): string
    {
        $value_width = 12 - $label_width;
        return
            '<div class="form-group">
                <label class="control-label col-sm-' . $label_width . '  ppr-pr-0">' . $label_text . '</label>
                <div class="col-sm-' . $value_width . '">
                    <p class="form-control-static">' . zen_output_string_protected($value_text) . '</p>
                </div>
            </div>';
    }

    protected function createModalInput(int $label_width, string $label_text, string $input_value, string $element_id, string $input_name, string $parameters, string $help_text = ''): string
    {
        $value_width = 12 - $label_width;
        if ($parameters !== '') {
            $parameters = " $parameters";
        }
        if ($help_text !== '') {
            $help_text = '<span class="help-block">' . $help_text . '</span>';
        }
        return
            '<div class="form-group">
                <label class="control-label col-sm-' . $label_width . '" for="' . $element_id . '">' . $label_text . '</label>
                <div class="col-sm-' . $value_width . '">
                    <input name="' . $input_name . '" class="form-control" id="' . $element_id . '" value="' . $input_value . '"' . $parameters . '>
                </div>
            </div>';
    }

    protected function createModalTextArea(int $label_width, string $label_text, string $default_message, string $element_id, string $textarea_name): string
    {
        $value_width = 12 - $label_width;
        return
            '<div class="form-group">
                <label class="control-label col-sm-' . $label_width . '" for="' . $element_id . '">' . $label_text . '</label>
                <div class="col-sm-' . $value_width . '">
                    <textarea name="' . $textarea_name . '" class="form-control" rows="5" id="' . $element_id . '">' . $default_message . '</textarea>
                </div>
            </div>';
    }

    protected function createModalCheckbox(int $label_width, string $label_text, string $checkbox_name): string
    {
        $value_width = 12 - $label_width;
        return
            '<div class="form-group">
                <div class="col-md-offset-' . $label_width . ' col-md-' . $value_width . '">
                    <div class="checkbox">
                        <label><input type="checkbox" name="' . $checkbox_name . '"> ' . $label_text . '</label>
                    </div>
                </div>
            </div>';
    }

    protected function createModalButtons(string $submit_button_id, string $toggle_button_name, string $submit_button_name): string
    {
        defined('TEXT_PLEASE_WAIT') || define('TEXT_PLEASE_WAIT', 'Please wait ...');
        return
            '<div class="btn-group btn-group-justified ppr-button-row">
                <div class="btn-group">
                    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#' . $submit_button_id . '">' . $toggle_button_name . '</button>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger collapse" id="' . $submit_button_id . '">' . $submit_button_name . '</button>
                    <script>document.getElementById("' . $submit_button_id . '").addEventListener("click", event => setTimeout(() => {event.target.disabled = true; event.target.innerHTML="' . TEXT_PLEASE_WAIT . '";}, 0)); </script>
                </div>
            </div>';
    }

    protected function createModal(string $modal_id, string $modal_title, string $modal_body, string $modal_size = 'md'): string
    {
        return
            '<div id="' . $modal_id . 'Modal" class="modal fade ppr-modal">
                <div class="modal-dialog modal-' . $modal_size . '">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title text-center">' . $modal_title . '</h4>
                        </div>
                        <div class="modal-body">' . $modal_body . '</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                   </div>
              </div>
        </div>';
    }

    protected function buildPaymentTableData(): string
    {
        // -----
        // Sort this order's PayPal transactions by date (oldest to newest).
        //
        $sorted_transactions = $this->paypalDbTxns;
        uasort($sorted_transactions, function($a, $b) {
            if ($a['date_added'] === $b['date_added']) {
                return 0;
            }
            return ($a['date_added'] < $b['date_added']) ? -1 : 1;
        });

        $paypal_gross_total = 0;
        $paypal_fees_total = 0;
        $settled_total = 0;

        $data = '';
        foreach ($sorted_transactions as $next_txn) {
            if ($next_txn['txn_type'] === 'CREATE' || $next_txn['settle_amount'] === null) {
                continue;
            }

            $data .=
                "<tr class=\"dataTableRow\">\n";

            foreach (self::$paymentTableFields as $next_field) {
                // -----
                // Retrieve the field's value, converting it to an 'amount' if so indicated.
                //
                $value = $next_txn[$next_field['field']];
                if (isset($next_field['is_amount']) && $value !== null) {
                    $value = $this->amount->getValueFromString($value);
                }

                // -----
                // Determine the currency in which the payment/refund was placed.
                //
                $mc_currency = $next_txn['mc_currency'];

                // -----
                // Calculations for the current PayPal settled amounts.
                //
                switch ($next_field['field']) {
                    // -----
                    // Special case for 'payment_status' field, it's followed by its "pending_reason",
                    // if present.
                    //
                    case 'payment_status':
                        if ($next_txn['pending_reason'] !== null) {
                            $value .= '<br><small>' . $next_txn['pending_reason'] . '</small>';
                        }
                        break;

                    // -----
                    // Capture any exchange_rate, since refunds are not converted in the
                    // the site's settlement currency.
                    //
                    case 'exchange_rate':
                        if (!empty($value)) {
                            $exchange_rate = $value;
                        }
                        break;

                    // -----
                    // Special case for 'mc_gross' field, it's followed by its "mc_currency",
                    // if present.
                    //
                    case 'mc_gross':
                        $value .= ' ' . $mc_currency;
                        break;

                    // -----
                    // Payment fees are summed up for the totals row.
                    //
                    case 'payment_fee':
                        if ($next_txn['txn_type'] === 'REFUND') {
                            $value = "<s>$value $mc_currency</s><sup>1</sup>";
                        } else {
                            $paypal_fees_total += $value;
                            $value = "$value $mc_currency";
                        }
                        break;

                    // -----
                    // Gross payments are summed up for the totals row, subtracted from
                    // the running total if the transaction was a REFUND; otherwise added.
                    //
                    case 'payment_gross':
                        if ($next_txn['txn_type'] === 'REFUND') {
                            $paypal_gross_total -= $value;
                            $value = "-$value";
                        } else {
                            $paypal_gross_total += $value;
                        }
                        $value .= ' ' . $mc_currency;
                        break;

                    // -----
                    // Settled amounts are summed up for the totals row, subtracted from
                    // the running total if the transaction was a REFUND; otherwise added.
                    //
                    case 'settle_amount':
                        $settle_currency = $settle_currency ?? $next_txn['settle_currency'];
                        if ($next_txn['txn_type'] !== 'REFUND') {
                            $settled_total += $value;
                        } else {
                            $value = $this->amount->getValueFromString((string)($value + $next_txn['payment_fee']));
                            $value *= $exchange_rate ?? 1.00;
                            $settled_total -= $value;
                            $value = '-' . $this->amount->getValueFromFloat($value);
                        }
                        $value .= ' ' . $settle_currency;
                        break;

                    default:
                        if (empty($value) || $value === '0001-01-01 00:00:00') {
                            $value = '&mdash;';
                        }
                        break;
                }

                $align_class = (isset($next_field['align'])) ? " text-{$next_field['align']}" : '';
                $data .=
                    "  <td class=\"dataTableContent$align_class\">$value</td>\n";
            }
        }

        // -----
        // If no settlements are recorded, return a table-row indicating as such.
        //
        $column_count = count(self::$paymentTableFields);
        if ($data === '') {
            return
                "<tr class=\"dataTableRow ppr-no-payments\">\n" .
                    "<td class=\"dataTableContents text-center\" colspan=\"$column_count\">" . MODULE_PAYMENT_PAYPALR_PAYMENTS_NONE . "</td>\n" .
                "</tr>\n";
        }

        // -----
        // Otherwise, add a table-entry for the current payments' totals.
        //
        $paypal_gross_total = $this->amount->getValueFromString((string)$paypal_gross_total);
        $paypal_fees_total = $this->amount->getValueFromString((string)$paypal_fees_total);
        $settled_total = $this->amount->getValueFromString((string)$settled_total);
        $column_count -= 3;
        $data .=
            "<tr class=\"dataTableHeadingRow text-right ppr-payments\">\n" .
                "<td class=\"dataTableHeadingContent\" colspan=\"$column_count\">" . MODULE_PAYMENT_PAYPALR_PAYMENTS_TOTAL . "</td>\n" .
                "<td class=\"dataTableHeadingContent\">" . $paypal_gross_total . ' ' . $mc_currency . "</td>\n" .
                "<td class=\"dataTableHeadingContent\">" . $paypal_fees_total . ' ' . $mc_currency . "</td>\n" .
                "<td class=\"dataTableHeadingContent\">" . $settled_total . ' ' . $settle_currency . "</td>\n" .
            "</tr>\n";

        return $data;
    }

    protected function loadJQuery(): string
    {
        $jquery = '';
        if ($this->jQueryLoadRequired === true) {
        }
        return $jquery;
    }
}
