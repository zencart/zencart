<?php
/**
 * A class that provides the overall layout of "Additional Handling Methods" when
 * an order in the Zen Cart admin placed with the PayPal Restful payment module.
 *
 * @copyright Copyright 2023-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.3.0
 */
namespace PayPalRestful\Admin;

use PayPalRestful\Admin\GetPayPalOrderTransactions;
use PayPalRestful\Admin\Formatters\MainDisplay;
use PayPalRestful\Api\PayPalRestfulApi;

class AdminMain
{
    protected $ppr;

    protected $adminNotifications = '';
    
    protected $externalTxnAdded = false;

    public function __construct(string $module_name, string $module_version, int $oID, PayPalRestfulApi $ppr)
    {
        $this->ppr = $ppr;

        // -----
        // Create an instance of the class that gathers the PayPal transactions recorded
        // in the database.
        //
        $ppr_txns = new GetPayPalOrderTransactions($module_name, $module_version, $oID, $ppr);

        // -----
        // Synchronize with PayPal, just in case a transaction was updated in the management
        // console (not via the Zen Cart admin processing).
        //
        $ppr_txns->syncPaypalTxns();

        // -----
        // Retrieve the PayPal transactions currently registered in the database
        // for subsequent display.
        //
        $paypal_db_txns = $ppr_txns->getDatabaseTxns();
        if (count($paypal_db_txns) === 0) {
            $this->adminNotifications =
                '<div class="alert alert-warning text-center">' .
                    '<strong>' . MODULE_PAYMENT_PAYPALR_NO_RECORDS_FOUND . '</strong>' .
                '</div>';
            return;
        }

        // -----
        // When the database transactions are retrieved, messages are created if
        // the PayPal synchronization resulted in new records being added.
        //
        $this->adminNotifications = $ppr_txns->getMessages();

        // -----
        // Format the main notification table displayed, showing the basic flow of
        // transactions for this order.
        //
        $main_display = new MainDisplay($paypal_db_txns);
        $this->adminNotifications .= $main_display->get();

        // -----
        // Record the processing flag that indicates whether/not PayPal transactions
        // were added outside of the PayPal Checkout payment module's processing.
        //
        $this->externalTxnAdded = $ppr_txns->externalTxnAdded();
    }

    public function externalTxnAdded(): bool
    {
        return $this->externalTxnAdded;
    }

    public function get()
    {
        return $this->adminNotifications;
    }
}
