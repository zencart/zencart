<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jul 10 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

define('TABLE_RECORD_ARTISTS', DB_PREFIX . 'record_artists');
define('TABLE_RECORD_ARTISTS_INFO', DB_PREFIX . 'record_artists_info');
define('TABLE_RECORD_COMPANY', DB_PREFIX . 'record_company');
define('TABLE_RECORD_COMPANY_INFO', DB_PREFIX . 'record_company_info');
define('TABLE_PRODUCT_MUSIC_EXTRA', DB_PREFIX . 'product_music_extra');
define('TABLE_MUSIC_GENRE', DB_PREFIX . 'music_genre');
define('TABLE_MUSIC_GENRE_INFO', DB_PREFIX . 'music_genre_info');
define('TABLE_MEDIA_MANAGER', DB_PREFIX . 'media_manager');
define('TABLE_MEDIA_TYPES', DB_PREFIX . 'media_types');
define('TABLE_MEDIA_CLIPS', DB_PREFIX . 'media_clips');
define('TABLE_MEDIA_TO_PRODUCTS', DB_PREFIX . 'media_to_products');
?>
