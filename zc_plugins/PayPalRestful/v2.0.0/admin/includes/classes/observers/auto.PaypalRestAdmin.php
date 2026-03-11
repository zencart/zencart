<?php
/**
 * Part of the paypalr (PayPal Restful Api) payment module.
 * Admin handles package tracking updates.
 *
 * Last updated: v1.3.0
 */

use PayPalRestful\Api\Data\CountryCodes;
use PayPalRestful\Api\PayPalRestfulApi;
use PayPalRestful\Zc2Pp\Amount;

require_once DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypal/pprAutoload.php';

class zcObserverPaypalRestAdmin extends base
{
    protected $adminBeforeInsertDone = false;

    public function __construct()
    {
        // -----
        // If the paypalr payment-module isn't installed or isn't configured to be enabled,
        // then nothing further to do here.
        //
        if (!defined('MODULE_PAYMENT_PAYPALR_STATUS') || MODULE_PAYMENT_PAYPALR_STATUS !== 'True') {
            return;
        }

        if (!IS_ADMIN_FLAG) {
            return;
        }
        $this->attach($this, ['ZEN_UPDATE_ORDERS_HISTORY_AFTER_INSERT']);
        if (zen_get_zcversion() < 2.2) {
            $this->attach($this, ['ZEN_UPDATE_ORDERS_HISTORY_BEFORE_INSERT']);
        }
    }

    /**
     * @param array $data [int orders_id, int orders_status_id, date_added, int customer_notified, comments, updated_by]
     */
    public function updateZenUpdateOrdersHistoryBeforeInsert(&$class, $eventID, $null, array $data)
    {
        $this->updateZenUpdateOrdersHistoryAfterInsert($class, $eventID, 0, $data);
        $this->detach($this, ['ZEN_UPDATE_ORDERS_HISTORY_BEFORE_INSERT']);
        $this->adminBeforeInsertDone = true;
    }

    /**
     * @param array $data [int orders_id, int orders_status_id, date_added, int customer_notified, comments, updated_by]
     */
    public function updateZenUpdateOrdersHistoryAfterInsert(&$class, $eventID, int $osh_id, array $data)
    {
        if ($this->adminBeforeInsertDone) {
            // avoid double-processing when attached to an older version's ZEN_UPDATE_ORDERS_HISTORY_BEFORE_INSERT
            return;
        }
        // Parse POST for tracking IDs. Depends on Ty Package Tracking installed.
        $track_ids = [];
        for ($i = 1; $i <= 5; $i++) {
            $track_id_var = "track_id$i";
            if (empty($_POST[$track_id_var])) {
                continue;
            }
            $track_ids[$i] = str_replace(' ', '', zen_db_prepare_input($_POST[$track_id_var]));
        }
        // Abort if no tracking IDs found.
        if (count($track_ids) === 0) {
            return;
        }
        $order_id = (int)$data['orders_id'];
        // Lookup the initial PayPal transaction record related to this order.
        $paypalLookup = $GLOBALS['db']->Execute(
            "SELECT txn_id, txn_type
                 FROM " . TABLE_PAYPAL . "
                 WHERE order_id = $order_id
                 ORDER BY date_added, parent_txn_id, paypal_ipn_id", 1
        );
        $paypal = $paypalLookup->EOF ? [] : $paypalLookup->fields;
        if (empty($paypal)) {
            return;
        }

        require_once DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/paypalr.php';
        list($client_id, $secret) = \paypalr::getEnvironmentInfo();
        $ppr = new PayPalRestfulApi(MODULE_PAYMENT_PAYPALR_SERVER, $client_id, $secret);

        foreach ($track_ids as $i => $tracking_number) {
            if (empty($tracking_number)) {
                continue;
            }
            // Add the tracking number to the PayPal transaction.
            $carrier_name = defined("CARRIER_NAME_$i") && !empty("CARRIER_NAME_$i") ? constant("CARRIER_NAME_$i") : 'OTHER';
            $result = $ppr->updatePackageTracking($paypal['txn_id'], $tracking_number, $carrier_name, 'ADD');
        }

        // De-register, to prevent multiple insertions in this cycle.
        $this->detach($this, ['ZEN_UPDATE_ORDERS_HISTORY_AFTER_INSERT']);
    }
}

if (!function_exists('zen_get_zcversion')) {
    /** @since ZC v1.5.7 */
    function zen_get_zcversion()
    {
        return PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR;
    }
}
