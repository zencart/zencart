<?php
/**
 * @package languageDefines
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 May 13 Modified in v1.5.6b $
 */
/*

The credit card define statements match the actual records in the configuration table.

For example for Visa:
TEXT_CC_ENABLED_VISA or IMAGE_CC_ENABLED_VISA is used for CC_ENABLED_VISA that is stored in the configuration table

If there is a new credit card added but there is not a matching define it cannot be used by the function zen_get_cc_enabled()

To obtain a list of accepted credit cards use the function zen_get_cc_enabled()

Example:

echo TEXT_ACCEPTED_CREDIT_CARDS . zen_get_cc_enabled();

*/

if (!defined('SHOW_ACCEPTED_CREDIT_CARDS')) {
    return;
}

define('TEXT_ACCEPTED_CREDIT_CARDS', '<strong>We accept:</strong> ');

// cc enabled text
define('TEXT_CC_ENABLED_VISA','Visa');
define('TEXT_CC_ENABLED_MC','MC');
define('TEXT_CC_ENABLED_AMEX','AmEx');
define('TEXT_CC_ENABLED_DINERS_CLUB','Diners Club');
define('TEXT_CC_ENABLED_DISCOVER','Discover');
define('TEXT_CC_ENABLED_JCB','JCB');
define('TEXT_CC_ENABLED_AUSTRALIAN_BANKCARD','Australian Bankcard');
define('TEXT_CC_ENABLED_SOLO','Solo');
define('TEXT_CC_ENABLED_DEBIT','Debit');
define('TEXT_CC_ENABLED_MAESTRO','Maestro');

// for images define these as:
// define('IMAGE_CC_ENABLED_VISA',zen_image(DIR_WS_IMAGES . 'filename.jpg');
// use the function
// echo zen_get_cc_enabled('IMAGE_');

// cc images
if (SHOW_ACCEPTED_CREDIT_CARDS == '2') {
    if (CC_ENABLED_VISA == '1') {
        define('IMAGE_CC_ENABLED_VISA', zen_image($template->get_template_dir('cc1.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc1.gif'));
    }
    if (CC_ENABLED_MC == '1') {
        define('IMAGE_CC_ENABLED_MC', zen_image($template->get_template_dir('cc2.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc2.gif'));
    }
    if (CC_ENABLED_AMEX == '1') {
        define('IMAGE_CC_ENABLED_AMEX', zen_image($template->get_template_dir('cc3.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc3.gif'));
    }
    if (CC_ENABLED_DINERS_CLUB == '1') {
        define('IMAGE_CC_ENABLED_DINERS_CLUB', zen_image($template->get_template_dir('cc4.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc4.gif'));
    }
    if (CC_ENABLED_DISCOVER == '1') {
        define('IMAGE_CC_ENABLED_DISCOVER', zen_image($template->get_template_dir('cc5.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc5.gif'));
    }
    if (CC_ENABLED_JCB == '1') {
        define('IMAGE_CC_ENABLED_JCB', zen_image($template->get_template_dir('cc6.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc6.gif'));
    }
    if (CC_ENABLED_AUSTRALIAN_BANKCARD == '1') {
        define('IMAGE_CC_ENABLED_AUSTRALIAN_BANKCARD', zen_image($template->get_template_dir('cc7.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc7.gif'));
    }
    if (CC_ENABLED_SOLO == '1') {
        define('IMAGE_CC_ENABLED_SOLO', zen_image($template->get_template_dir('cc8.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc8.gif'));
    }
    if (CC_ENABLED_DEBIT == '1') {
        define('IMAGE_CC_ENABLED_DEBIT', zen_image($template->get_template_dir('cc9.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc9.gif'));
    }
    if (CC_ENABLED_MAESTRO == '1') {
        define('IMAGE_CC_ENABLED_MAESTRO', zen_image($template->get_template_dir('cc10.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc10.gif'));
    }
}
