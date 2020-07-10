<?php
/**
 * cc_validation Class.
 *
 * @package classes
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Mon Jul 27 18:24:22 2015 -0400 Modified in v1.5.5 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * cc_validation Class.
 * Class to validate credit card numbers etc
 *
 * @package classes
 */
class cc_validation extends base {
  var $cc_type, $cc_number, $cc_expiry_month, $cc_expiry_year;

  function validate($number, $expiry_m, $expiry_y, $start_m = null, $start_y = null) {
    $this->cc_number = preg_replace('/[^0-9]/', '', $number);
    // NOTE: We check Solo before Maestro, and Maestro/Switch *before* we check Visa/Mastercard, so we don't have to rule-out numerous types from V/MC matching rules.
    if (preg_match('/^(6334[5-9][0-9]|6767[0-9]{2})[0-9]{10}([0-9]{2,3}?)?$/', $this->cc_number) && CC_ENABLED_SOLO=='1') {
      $this->cc_type = "Solo"; // is also a Maestro product
    } else if (preg_match('/^(49369[8-9]|490303|6333[0-4][0-9]|6759[0-9]{2}|5[0678][0-9]{4}|6[0-9][02-9][02-9][0-9]{2})[0-9]{6,13}?$/', $this->cc_number) && CC_ENABLED_MAESTRO=='1') {
      $this->cc_type = "Maestro";
    } else if (preg_match('/^(49030[2-9]|49033[5-9]|4905[0-9]{2}|49110[1-2]|49117[4-9]|49918[0-2]|4936[0-9]{2}|564182|6333[0-4][0-9])[0-9]{10}([0-9]{2,3}?)?$/', $this->cc_number) && CC_ENABLED_MAESTRO=='1') {
      $this->cc_type = "Maestro"; // SWITCH is now Maestro
    } elseif (preg_match('/^4[0-9]{12}([0-9]{3})?$/', $this->cc_number) && CC_ENABLED_VISA=='1') {
      $this->cc_type = 'Visa';
	} elseif (preg_match('/^(5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/', $this->cc_number) && CC_ENABLED_MC=='1') {
      $this->cc_type = 'MasterCard'; // 510000-550000, 222100-272099
    } elseif (preg_match('/^3[47][0-9]{13}$/', $this->cc_number) && CC_ENABLED_AMEX=='1') {
      $this->cc_type = 'American Express';
    } elseif (preg_match('/^3(0[0-5]|[68][0-9])[0-9]{11}$/', $this->cc_number) && CC_ENABLED_DINERS_CLUB=='1') {
      $this->cc_type = 'Diners Club';
    } elseif (preg_match('/^(6011[0-9]{12}|622[1-9][0-9]{12}|64[4-9][0-9]{13}|65[0-9]{14})$/', $this->cc_number) && CC_ENABLED_DISCOVER=='1') {
      $this->cc_type = 'Discover';
    } elseif (preg_match('/^(35(28|29|[3-8][0-9])[0-9]{12}|2131[0-9]{11}|1800[0-9]{11})$/', $this->cc_number) && CC_ENABLED_JCB=='1') {
      $this->cc_type = "JCB";
    } elseif (preg_match('/^5610[0-9]{12}$/', $this->cc_number) && CC_ENABLED_AUSTRALIAN_BANKCARD=='1') {
      $this->cc_type = 'Australian BankCard'; // NOTE: is now obsolete
    } else {
      return -1;
    }

    if (is_numeric($expiry_m) && ($expiry_m > 0) && ($expiry_m < 13)) {
      $this->cc_expiry_month = $expiry_m;
    } else {
      return -2;
    }

    $current_year = date('Y');
    if (strlen($expiry_y) == 2) $expiry_y = intval(substr($current_year, 0, 2) . $expiry_y);
    if (is_numeric($expiry_y) && ($expiry_y >= $current_year) && ($expiry_y <= ($current_year + 10))) {
      $this->cc_expiry_year = $expiry_y;
    } else {
      return -3;
    }

    if ($expiry_y == $current_year) {
      if ($expiry_m < date('n')) {
        return -4;
      }
    }

    // check the issue month & year but only for Switch/Solo cards
    if (($start_m || $start_y) && in_array($this->cc_type, array('Switch', 'Solo'))) {
      if (!(is_numeric($start_m) && ($start_m > 0) && ($start_m < 13))) {
        return -2;
      }

      if (strlen($start_y) == 2) {
        if ($start_y > 80) {
          $start_y = intval('19' . $start_y);
        } else {
          $start_y = intval('20' . $start_y);
        }
      }

      if (!is_numeric($start_y) || ($start_y > $current_year)) {
        return -3;
      }
      if (!($start_y >= ($current_year - 10))) {
        return -3;
      }
    }
    return $this->is_valid();
  }

  function is_valid() {
    $cardNumber = strrev($this->cc_number);
    $numSum = 0;

    for ($i=0; $i<strlen($cardNumber); $i++) {
      $currentNum = substr($cardNumber, $i, 1);

      // Double every second digit
      if ($i % 2 == 1) {
        $currentNum *= 2;
      }

      // Add digits of 2-digit numbers together
      if ($currentNum > 9) {
        $firstNum = $currentNum % 10;
        $secondNum = ($currentNum - $firstNum) / 10;
        $currentNum = $firstNum + $secondNum;
      }

      $numSum += $currentNum;
    }

    // If the total has no remainder it's OK
    return ($numSum % 10 == 0);
  }
}
