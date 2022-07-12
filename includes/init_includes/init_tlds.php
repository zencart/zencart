<?php
/**
 * set some top level domain variables
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Aug 01 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
 die('Illegal Access');
} 
$http_domain = zen_get_top_level_domain(HTTP_SERVER);
$https_domain = zen_get_top_level_domain(HTTPS_SERVER);
$cookieDomain = $current_domain = (($request_type == 'NONSSL') ? $http_domain : $https_domain);
if (defined('HTTP_COOKIE_DOMAIN') && ($request_type == 'NONSSL'))
{
  $cookieDomain = HTTP_COOKIE_DOMAIN;
} elseif (defined('HTTPS_COOKIE_DOMAIN') && ($request_type != 'NONSSL')) 
{
  $cookieDomain = HTTPS_COOKIE_DOMAIN;
}
