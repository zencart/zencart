<?php
/**
 * A collection of 'helper' methods for the PayPalRestful (paypalr) Payment Module
 *
 * @copyright Copyright 2023-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * Last updated: v1.0.3
 */

namespace PayPalRestful\Common;

class Helpers
{
    public static function arrayDiffRecursive(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = self::arrayDiffRecursive($value, $array2[$key]);
                    if (!empty($new_diff)) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $difference[$key] = $value;
            }
        }
        return $difference;
    }

    public static function convertPayPalDatePay2Db(string $paypal_date): string
    {
        if (!function_exists('convertToLocalTimeZone')) {
            return self::convertToLocalTimeZone(trim(preg_replace('/[^0-9-:]/', ' ', $paypal_date)));
        } else {
            return convertToLocalTimeZone(trim(preg_replace('/[^0-9-:]/', ' ', $paypal_date)));
        }
    }

    // -----
    // Required for zc158a; function was not defined until zc200!
    //
    protected static function convertToLocalTimeZone(string $dateTime, string $fromTz = 'UTC', string $outputFormat = 'Y-m-d H:i:s'): string
    {
        $localDateTime = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $localDateTime->setTimezone((new \DateTime)->getTimezone());
        return $localDateTime->format($outputFormat);
    }

    public static function getDaysTo(string $future_date): string
    {
        return (string)ceil((strtotime($future_date) - time()) / 86400);
    }

    public static function getDaysFrom(string $past_date): string
    {
        return (string)ceil((time() - strtotime($past_date)) / 86400);
    }

    public static function getCustomerNameSuffix(): string
    {
        $substr_function = (function_exists('mb_substr')) ? 'mb_substr' : 'substr';
        $log_suffix = $substr_function($_SESSION['customer_first_name'] ?? 'na', 0, 3) . $substr_function($_SESSION['customer_last_name'] ?? 'na', 0, 3);
        if (function_exists('mb_ereg_replace')) {
            return mb_ereg_replace('[^a-zA-Z0-9]', '_', $log_suffix);
        }
        return preg_replace('/[^a-zA-Z0-9]/', '_', $log_suffix);
    }
}
