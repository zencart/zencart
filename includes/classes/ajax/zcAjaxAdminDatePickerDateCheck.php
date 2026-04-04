<?php
/**
 * zcAjaxAdminDateCheck
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v2.0.0
 */
class zcAjaxAdminDatePickerDateCheck extends base
{
    /**
     * check.  Checks a 'datepicker' date for validity
     *
     * @since ZC v2.0.0
     */
    public function check()
    {
        // -----
        // Deny access unless running under the admin.
        //
        if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true || !isset($_POST['date_to_check'])) {
            return 'false';
        }

        // -----
        // If the submitted date is an empty string, that's valid.
        //
        $date_raw = $_POST['date_to_check'];
        if ($date_raw === '') {
            return 'true';
        }

        if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd') {
            $local_fmt = zen_datepicker_format_fordate();
            $dt = DateTime::createFromFormat($local_fmt, $date_raw);
            $date_raw = false;
            if (!empty($dt)) {
              $date_raw = $dt->format('Y-m-d');
            }
        }
        return ($date_raw !== false && zcDate::validateDate($date_raw) === true) ? 'true' : 'false';
    }
}
