<?php
/**
 * define_queries
 * defines queries used in various codeblocks
 * can be used to assist with special requirements for other database-abstraction configurations
 *
 * @package classes
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: define_queries.php 4587 2006-09-23 00:46:06Z ajeh $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
DEFINE('SQL_CC_ENABLED', "select configuration_key from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'CC_ENABLED' and configuration_value= '1'");
DEFINE('SQL_SHOW_PRODUCT_INFO_CATEGORY', "select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'SHOW_PRODUCT_INFO_CATEGORY' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_MAIN',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'SHOW_PRODUCT_INFO_MAIN' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_MISSING',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION  . " where configuration_key RLIKE 'SHOW_PRODUCT_INFO_MISSING' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_SHOW_PRODUCT_INFO_LISTING_BELOW',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'SHOW_PRODUCT_INFO_LISTING_BELOW' and configuration_value > 0 order by configuration_value");
DEFINE('SQL_BANNER_CHECK_QUERY', "select count(*) as count from " . TABLE_BANNERS_HISTORY . "                where banners_id = '%s' and date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
DEFINE('SQL_BANNER_CHECK_UPDATE', "update " . TABLE_BANNERS_HISTORY . " set banners_shown = banners_shown +1 where banners_id = '%s' and date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
DEFINE('SQL_BANNER_UPDATE_CLICK_COUNT', "update " . TABLE_BANNERS_HISTORY . " set banners_clicked = banners_clicked + 1 where banners_id = '%s' and date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
DEFINE('SQL_ALSO_PURCHASED', "SELECT p.products_id, p.products_image
                     FROM " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, "
                            . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p
                     WHERE opa.products_id = '%s'
                     AND opa.orders_id = opb.orders_id
                     AND opb.products_id != '%s'
                     AND opb.products_id = p.products_id
                     AND opb.orders_id = o.orders_id
                     AND p.products_status = 1
                     GROUP BY p.products_id, p.products_image
                     ORDER BY o.date_purchased desc
                     LIMIT " . MAX_DISPLAY_ALSO_PURCHASED);
DEFINE('SQL_SHOW_SHOPPING_CART_EMPTY',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'SHOW_SHOPPING_CART_EMPTY' and configuration_value > 0 order by configuration_value");
