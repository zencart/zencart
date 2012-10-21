<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_tlds.php 16435 2010-05-28 09:34:32Z drbyte $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$http_domain = zen_get_top_level_domain(HTTP_SERVER);
$cookieDomain = $https_domain;
if (defined('HTTP_COOKIE_DOMAIN'))
{
  $cookieDomain = HTTP_COOKIE_DOMAIN;
}