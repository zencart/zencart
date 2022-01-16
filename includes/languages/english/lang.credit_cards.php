<?php
if (!defined('SHOW_ACCEPTED_CREDIT_CARDS')) {
    return;
}
global $template, $current_page_base; 
$define = [
    'TEXT_ACCEPTED_CREDIT_CARDS' => '<strong>We accept:</strong> ',
    'TEXT_CC_ENABLED_VISA' => 'Visa',
    'TEXT_CC_ENABLED_MC' => 'MC',
    'TEXT_CC_ENABLED_AMEX' => 'AmEx',
    'TEXT_CC_ENABLED_DINERS_CLUB' => 'Diners Club',
    'TEXT_CC_ENABLED_DISCOVER' => 'Discover',
    'TEXT_CC_ENABLED_JCB' => 'JCB',
    'TEXT_CC_ENABLED_AUSTRALIAN_BANKCARD' => 'Australian Bankcard',
    'TEXT_CC_ENABLED_SOLO' => 'Solo',
    'TEXT_CC_ENABLED_DEBIT' => 'Debit',
    'TEXT_CC_ENABLED_MAESTRO' => 'Maestro',
    'IMAGE_CC_ENABLED_VISA' => zen_image($template->get_template_dir('cc1.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc1.gif'),
    'IMAGE_CC_ENABLED_MC' => zen_image($template->get_template_dir('cc2.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc2.gif'),
    'IMAGE_CC_ENABLED_AMEX' => zen_image($template->get_template_dir('cc3.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc3.gif'),
    'IMAGE_CC_ENABLED_DINERS_CLUB' => zen_image($template->get_template_dir('cc4.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc4.gif'),
    'IMAGE_CC_ENABLED_DISCOVER' => zen_image($template->get_template_dir('cc5.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc5.gif'),
    'IMAGE_CC_ENABLED_JCB' => zen_image($template->get_template_dir('cc6.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc6.gif'),
    'IMAGE_CC_ENABLED_AUSTRALIAN_BANKCARD' => zen_image($template->get_template_dir('cc7.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc7.gif'),
    'IMAGE_CC_ENABLED_SOLO' => zen_image($template->get_template_dir('cc8.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc8.gif'),
    'IMAGE_CC_ENABLED_DEBIT' => zen_image($template->get_template_dir('cc9.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc9.gif'),
    'IMAGE_CC_ENABLED_MAESTRO' => zen_image($template->get_template_dir('cc10.gif', DIR_WS_TEMPLATE, $current_page_base, 'images/icons') . '/' . 'cc10.gif'),
];

return $define;
