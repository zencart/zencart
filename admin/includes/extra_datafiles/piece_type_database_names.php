<?php
/**
 * @package admin
 * @copyright Copyright 2003-2006 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: piece_type_database_names.php 3001 2006-02-09 21:45:06Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

define('TABLE_ARTISTS', DB_PREFIX . 'artists');
define('TABLE_ARTISTS_INFO', DB_PREFIX . 'artists_info');
define('TABLE_AGENCY', DB_PREFIX . 'agency');
define('TABLE_AGENCY_INFO', DB_PREFIX . 'agency_info');
define('TABLE_PRODUCT_PIECE_EXTRA', DB_PREFIX . 'product_piece_extra');
define('TABLE_PIECE_STYLE', DB_PREFIX . 'piece_style');
define('TABLE_PIECE_STYLE_INFO', DB_PREFIX . 'piece_style_info');
define('TABLE_MEDIA_MANAGER', DB_PREFIX . 'media_manager');
define('TABLE_MEDIA_TYPES', DB_PREFIX . 'media_types');
define('TABLE_MEDIA_CLIPS', DB_PREFIX . 'media_clips');
define('TABLE_MEDIA_TO_PRODUCTS', DB_PREFIX . 'media_to_products');
?>
