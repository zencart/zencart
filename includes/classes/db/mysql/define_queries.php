<?php
/**
 * define_queries
 * defines queries used in various codeblocks
 * can be used to assist with special requirements for other database-abstraction configurations
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2020 Feb 15 Modified in v1.5.7 $
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
DEFINE('SQL_ALSO_PURCHASED', "SELECT p.products_id, p.products_image, max(o.date_purchased) as date_purchased
                     FROM " . TABLE_ORDERS_PRODUCTS . " opa, " . TABLE_ORDERS_PRODUCTS . " opb, "
                            . TABLE_ORDERS . " o, " . TABLE_PRODUCTS . " p
                     WHERE opa.products_id = %u
                     AND opa.orders_id = opb.orders_id
                     AND opb.products_id != %u
                     AND opb.products_id = p.products_id
                     AND opb.orders_id = o.orders_id
                     AND p.products_status = 1
                     GROUP BY p.products_id, p.products_image
                     ORDER BY date_purchased desc, p.products_id
                     LIMIT %u");
DEFINE('SQL_SHOW_SHOPPING_CART_EMPTY',"select configuration_key, configuration_value from " . TABLE_CONFIGURATION . " where configuration_key RLIKE 'SHOW_SHOPPING_CART_EMPTY' and configuration_value > 0 order by configuration_value");
