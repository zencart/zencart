<?php
/**
 * define_queries
 * defines queries used in various codeblocks
 * can be used to assist with special requirements for other database-abstraction configurations
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 14 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
define('SQL_CC_ENABLED', "SELECT configuration_key FROM " . TABLE_CONFIGURATION . " WHERE configuration_key RLIKE 'CC_ENABLED' AND configuration_value= '1'");
define('SQL_SHOW_PRODUCT_INFO_CATEGORY', "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key RLIKE 'SHOW_PRODUCT_INFO_CATEGORY' AND configuration_value > 0 ORDER BY configuration_value");
define('SQL_SHOW_PRODUCT_INFO_MAIN',"SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key RLIKE 'SHOW_PRODUCT_INFO_MAIN' AND configuration_value > 0 ORDER BY configuration_value");
define('SQL_SHOW_PRODUCT_INFO_MISSING',"SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION  . " WHERE configuration_key RLIKE 'SHOW_PRODUCT_INFO_MISSING' AND configuration_value > 0 ORDER BY configuration_value");
define('SQL_SHOW_PRODUCT_INFO_LISTING_BELOW',"SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key RLIKE 'SHOW_PRODUCT_INFO_LISTING_BELOW' AND configuration_value > 0 ORDER BY configuration_value");
define('SQL_BANNER_CHECK_QUERY', "SELECT count(*) AS count FROM " . TABLE_BANNERS_HISTORY . " WHERE banners_id = '%s' AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
define('SQL_BANNER_CHECK_UPDATE', "update " . TABLE_BANNERS_HISTORY . " SET banners_shown = banners_shown +1 WHERE banners_id = '%s' AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
define('SQL_BANNER_UPDATE_CLICK_COUNT', "UPDATE " . TABLE_BANNERS_HISTORY . " SET banners_clicked = banners_clicked + 1 WHERE banners_id = '%s' AND date_format(banners_history_date, '%%Y%%m%%d') = date_format(now(), '%%Y%%m%%d')");
define('SQL_ALSO_PURCHASED', "SELECT p.products_id, p.products_image, max(o.date_purchased) AS date_purchased
                     FROM " . TABLE_ORDERS_PRODUCTS . " opa
                     INNER JOIN " . TABLE_ORDERS_PRODUCTS . " opb ON (opa.orders_id = opb.orders_id)
                     INNER JOIN " . TABLE_ORDERS . " o ON (opb.orders_id = o.orders_id)
                     INNER JOIN " . TABLE_PRODUCTS . " p ON (opb.products_id = p.products_id)
                     WHERE opa.products_id = %u
                     AND opb.products_id != %u
                     AND p.products_status = 1
                     GROUP BY p.products_id, p.products_image
                     ORDER BY date_purchased desc, p.products_id");

define('SQL_SHOW_SHOPPING_CART_EMPTY',"SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key RLIKE 'SHOW_SHOPPING_CART_EMPTY' AND configuration_value > 0 ORDER BY configuration_value");
