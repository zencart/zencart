<?php
/**
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Piloujp 2024 Mar 8 Modified in v2.0.0-beta1 $
 */

// Function zen_date_raw override
    function zen_date_raw($date, $reverse = false) {
        // sometimes zen_date_short is called with a zero-date value which returns false, which is then passed to $date here, so this just reformats to avoid confusion.
        if (empty($date) || strpos($date, '0001') || strpos($date, '0000')) {
            $date = DateTime::createFromFormat('!m/d/Y', '01/01/0001')->format(DATE_FORMAT);
        }

        $date = preg_replace('/\D+/', '', $date);

        if (DATE_FORMAT === 'd/m/Y') {
            if ($reverse) {
                return substr($date, 0, 2) . substr($date, 2, 2) . substr($date, 4, 4);
            } else {
                return substr($date, 4, 4) . substr($date, 2, 2) . substr($date, 0, 2);
            }
        } elseif (DATE_FORMAT === 'Y/m/d') {
            if ($reverse) {
                return substr($date, 6, 2) . substr($date, 4, 2) . substr($date, 0, 4);
            } else {
                return substr($date, 0, 4) . substr($date, 4, 2) . substr($date, 6, 2);
            }
        } elseif ($reverse) {
            return substr($date, 2, 2) . substr($date, 0, 2) . substr($date, 4, 4);
        } else {
            return substr($date, 4, 4) . substr($date, 0, 2) . substr($date, 2, 2);
        }
    }
