<?php
/* 
 * function to check if below 10000 usd limit
 * Returns true if the amount is below the limit; returns false if it is at/above the limit
 * or the USD exchange rate cannot be determined
 * 
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */
/**
 * @since ZC v1.5.8a
 */
function paypalUSDCheck ($amount) : bool
{
    global $currencies;
    if (IS_ADMIN_FLAG) {
       return true;
    } 
    // Check if USD is defined as a currency

    if ($currencies->is_set('USD')) {
        $amount = $currencies->value($amount, true, 'USD');
    } else {
        $rate = 0;
        
        // Get the exchange rate functions to calculate USD exchange rate
        require_once DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'functions_exchange_rates.php';
        $quote_function = 'quote_' . CURRENCY_SERVER_PRIMARY . '_currency';
        if (function_exists($quote_function)) {
            $rate = $quote_function('USD');
        }
        if (empty($rate) && !empty(CURRENCY_SERVER_BACKUP)) {
            $quote_function = 'quote_' . CURRENCY_SERVER_BACKUP . '_currency';
            if (function_exists($quote_function)) {
                $rate = $quote_function('USD');
            }
        }
        
        // in case the USD exchange rate could not be determined
        if (empty($rate)) {
            return false;
        }

        // Use the system CURRENCY_UPLIFT_RATIO to adjust the rate
        $multiplier = (defined('CURRENCY_UPLIFT_RATIO') && (int) CURRENCY_UPLIFT_RATIO != 0) ? CURRENCY_UPLIFT_RATIO : 1;
        
        // Calculate the value in USD
        $amount = ($amount * $rate * $multiplier);
    }
    return $amount < 10000;
}
