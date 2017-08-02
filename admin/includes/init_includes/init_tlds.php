<?php
/**
 * @package admin
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Aug 2017 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$http_domain = zen_get_top_level_domain(HTTP_SERVER);
$cookieDomain = $http_domain;
if (defined('HTTP_COOKIE_DOMAIN'))
{
  $cookieDomain = HTTP_COOKIE_DOMAIN;
}