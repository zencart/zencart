<?php
/**
 * if gzip_compression is enabled, start to buffer the output
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 Tue Aug 21 23:25:22 2018 -0400 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (!(isset($_GET['main_page']) && $_GET['main_page'] == FILENAME_DOWNLOAD) && (int)GZIP_LEVEL >= 1 && $ext_zlib_loaded = extension_loaded('zlib') && trim(ini_get('output_handler')) == '') {
  if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
    @ini_set('zlib.output_compression', 1);
  }
  if (($ini_zlib_output_compression = (int)ini_get('zlib.output_compression')) < 1) {
    ob_start('ob_gzhandler');
  } else {
    @ini_set('zlib.output_compression_level', (int)GZIP_LEVEL);
  }
}
