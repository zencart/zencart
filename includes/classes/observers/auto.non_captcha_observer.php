<?php
/**
 * Designed for v1.5.7
 *
 * Observer class used to detect spam input
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2017-2019 CowboyGeek.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jul 15 New in v1.5.7 $
 */

class zcObserverNonCaptchaObserver extends base
{
    private $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_NONCAPTCHA_CHECK',
            'NOTIFY_CREATE_ACCOUNT_CAPTCHA_CHECK',
            'NOTIFY_CONTACT_US_CAPTCHA_CHECK',
            'NOTIFY_REVIEWS_WRITE_CAPTCHA_CHECK',
        ]);

        if (empty($_SESSION['antispam_fieldname'])) {
            $_SESSION['antispam_fieldname'] = $this->generate_random_string($this->chars, 10);
        }
        $GLOBALS['antiSpamFieldName'] = $_SESSION['antispam_fieldname'];
    }

    // This update method fires if no updateNotifyxxxxxx function is declared below to match the notifier hooks we're listening to
    public function update(&$class, $eventID, $paramsArray)
    {
        $this->testURLSpam();
        $this->testAntiSpamFields();
    }

    public function updateNotifyContactUsCaptchaCheck(&$class, $eventID, $paramsArray)
    {
        // sanitize the contact-us name field more aggressively
        $GLOBALS['name'] = zen_db_prepare_input(zen_sanitize_string($_POST['contactname']));

        // fire default tests
        $this->update($class, $eventID, $paramsArray);
    }

    protected function testAntiSpamFields()
    {
        if (!empty($_POST[$_SESSION['antispam_fieldname']]) || !empty($_POST['should_be_empty'])) {
            $GLOBALS['antiSpam'] = 'spam';
        }
    }

    protected function generate_random_string($input, $strength = 16)
    {
        $function = PHP_VERSION_ID >= 70000 ? 'random_int' : 'mt_rand';
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $input[$function(0, $input_length - 1)];
            $random_string .= $random_character;
        }

        return $random_string;
    }

    protected function testURLSpam()
    {
        $test_string = '';

        // Simple regex to identify presence of an (unwanted) URL
        $reg_exUrl = '~(https?|ftps?):/~';

        $fields = array(
            'firstname',
            'lastname',
            'contactname',
            'company',
            'street_address',
            'suburb',
            'city',
            'state',
            'zone_country_id',
            'nick',
            'customers_referral',
            'telephone',
            'fax',
            'email_format',
            'to_name',
            'subject',
            'passwordhintA',
            'review_text', // comment-out if you actually want to allow URLs for this
            'enquiry',     // comment-out if you actually want to allow URLs for this
        );

        // prepare for inspection
        foreach ($fields as $field) {
            if (!empty($_POST[$field])) {
                $test_string .= $_POST[$field];
            }
        }

        if (empty($test_string)) return;

        // inspect
        if(preg_match($reg_exUrl, $test_string)) {
            $GLOBALS['antiSpam'] = 'spam';
        }
    }

}

