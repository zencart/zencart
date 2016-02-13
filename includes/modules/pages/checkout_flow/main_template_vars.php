<?php
/**
 * Checkout Flow Page
 *
 * @package page
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: New in v1.6.0 $
 */
$body_code = $zcCheckoutManager->getCheckoutFlowStep()->getTemplateName();
$body_code = $template->get_template_dir('tpl_' . preg_replace('/.php/', '',$zcRequest->readGet('main_page')) . '_default.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_' . $body_code . '_default.php';
require($body_code);
