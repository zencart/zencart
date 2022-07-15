<?php
/**
 * jscript_addr_pulldowns
 *
 * handles pulldown menu dependencies for state/country selection
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 May 18 New in v1.5.8-alpha $
 */
if (SHOW_SHIPPING_ESTIMATOR_BUTTON === '2') {
    require $template->get_template_dir('zen_addr_pulldowns.php', DIR_WS_TEMPLATE, $current_page_base, 'jscript') . '/zen_addr_pulldowns.php';
}
