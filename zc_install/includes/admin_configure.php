<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Aug 28 17:13:32 2012 -0400 Modified in v1.5.1 $
 */

$file_contents =
'<'.'?php' . "\n" .
'/**' . "\n" .
' * @package Configuration Settings circa 1.5.2' . "\n" .
' * @copyright Copyright 2003-2013 Zen Cart Development Team' . "\n" .
' * @copyright Portions Copyright 2003 osCommerce' . "\n" .
' * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0' . "\n" .
' * File Built by zc_install on ' . date('Y-m-d h:i:s') . "\n" .
' */' . "\n" .
'' . "\n" .
'' . "\n" .
'' . '/*************** NOTE: This file is similar, but DIFFERENT from the "store" version of configure.php. ***********/' . "\n" .
'' . '/***************       The 2 files should be kept separate and not used to overwrite each other.      ***********/' . "\n" .
'' . "\n" .
'/**' . "\n" .
' * WE RECOMMEND THAT YOU USE SSL PROTECTION FOR YOUR ENTIRE ADMIN:' . "\n" .
' * To do that, make sure you use a "https:" URL for BOTH the HTTP_SERVER and HTTPS_SERVER entries:' . "\n" .
' */' . "\n" .
'  define(\'HTTP_SERVER\', \'' . $http_srvr_admin . '\');' . "\n" .
'  define(\'HTTPS_SERVER\', \'' . $https_server . '\');' . "\n" .
'  define(\'HTTP_CATALOG_SERVER\', \'' . $http_server . '\');' . "\n" .
'  define(\'HTTPS_CATALOG_SERVER\', \'' . $https_server . '\');' . "\n\n" .
'  // secure webserver for admin?  Valid choices are \'true\' or \'false\' (including quotes).' . "\n" .
'  define(\'ENABLE_SSL_ADMIN\', \'' . $this->getConfigKey('ENABLE_SSL_ADMIN') . '\');' . "\n\n" .
'  // secure webserver for storefront?  Valid choices are \'true\' or \'false\' (including quotes).' . "\n" .
'  define(\'ENABLE_SSL_CATALOG\', \'' . $this->getConfigKey('ENABLE_SSL') . '\');' . "\n" .
'' . "\n" .
'// NOTE: be sure to leave the trailing \'/\' at the end of these lines if you make changes!' . "\n" .
'// * DIR_WS_* = Webserver directories (virtual/URL)' . "\n" .
'  // these paths are relative to top of your webspace ... (ie: under the public_html or httpdocs folder)' . "\n" .
'  $t1 = parse_url(HTTP_SERVER);$p1 = $t1[\'path\'];$t2 = parse_url(HTTPS_SERVER);$p2 = $t2[\'path\'];' . "\n\n" .
'  define(\'DIR_WS_ADMIN\', preg_replace(\'#^\' . str_replace(\'-\', \'\-\', $p1) . \'#\', \'\', dirname($_SERVER[\'SCRIPT_NAME\'])) . \'/\');' . "\n" .
'  define(\'DIR_WS_CATALOG\', \'' . $http_catalog . '\');' . "\n" .
'  define(\'DIR_WS_HTTPS_ADMIN\', preg_replace(\'#^\' . str_replace(\'-\', \'\-\', $p2) . \'#\', \'\', dirname($_SERVER[\'SCRIPT_NAME\'])) . \'/\');' . "\n" .
'  define(\'DIR_WS_HTTPS_CATALOG\', \'' . $https_catalog . '\');' . "\n\n" .
'  define(\'DIR_WS_IMAGES\', \'images/\');' . "\n" .
'  define(\'DIR_WS_ICONS\', DIR_WS_IMAGES . \'icons/\');' . "\n" .
'  define(\'DIR_WS_CATALOG_IMAGES\', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . \'images/\');' . "\n" .
'  define(\'DIR_WS_CATALOG_TEMPLATE\', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . \'includes/templates/\');' . "\n" .
'  define(\'DIR_WS_INCLUDES\', \'includes/\');' . "\n" .
'  define(\'DIR_WS_BOXES\', DIR_WS_INCLUDES . \'boxes/\');' . "\n" .
'  define(\'DIR_WS_FUNCTIONS\', DIR_WS_INCLUDES . \'functions/\');' . "\n" .
'  define(\'DIR_WS_CLASSES\', DIR_WS_INCLUDES . \'classes/\');' . "\n" .
'  define(\'DIR_WS_MODULES\', DIR_WS_INCLUDES . \'modules/\');' . "\n" .
'  define(\'DIR_WS_LANGUAGES\', DIR_WS_INCLUDES . \'languages/\');' . "\n" .
'  define(\'DIR_WS_CATALOG_LANGUAGES\', HTTP_CATALOG_SERVER . DIR_WS_CATALOG . \'includes/languages/\');' . "\n" .
'' . "\n" .
'// * DIR_FS_* = Filesystem directories (local/physical)' . "\n" .
'  define(\'DIR_FS_ADMIN\', realpath(dirname(__FILE__) . \'/../\') . \'/\');' . "\n" .
'  //the following path is a COMPLETE path to your Zen Cart files. eg: /var/www/vhost/accountname/public_html/store/' . "\n" .
'  define(\'DIR_FS_CATALOG\', \'' . $this->getConfigKey('DIR_FS_CATALOG') . '/\');' . "\n\n" .
'  //the following path is a COMPLETE path to the /logs/ folder  eg: /var/www/vhost/accountname/public_html/store/logs ... and no trailing slash' . "\n" .
'  define(\'DIR_FS_LOGS\', \'' . $this->getConfigKey('DIR_FS_CATALOG') . '/logs\');' . "\n\n" .
'  define(\'DIR_FS_CATALOG_LANGUAGES\', DIR_FS_CATALOG . \'includes/languages/\');' . "\n" .
'  define(\'DIR_FS_CATALOG_IMAGES\', DIR_FS_CATALOG . \'images/\');' . "\n" .
'  define(\'DIR_FS_CATALOG_MODULES\', DIR_FS_CATALOG . \'includes/modules/\');' . "\n" .
'  define(\'DIR_FS_CATALOG_TEMPLATES\', DIR_FS_CATALOG . \'includes/templates/\');' . "\n" .
'  define(\'DIR_FS_BACKUP\', DIR_FS_ADMIN . \'backups/\');' . "\n" .
'  define(\'DIR_FS_EMAIL_TEMPLATES\', DIR_FS_CATALOG . \'email/\');' . "\n" .
'  define(\'DIR_FS_DOWNLOAD\', DIR_FS_CATALOG . \'download/\');' . "\n" .
'' . "\n" .
'// define our database connection' . "\n" .
'  define(\'DB_TYPE\', \'' . $this->getConfigKey('DB_TYPE'). '\');' . "\n" .
'  define(\'DB_PREFIX\', \'' . $this->getConfigKey('DB_PREFIX'). '\');' . "\n" .
'  define(\'DB_CHARSET\', \'' . $this->getConfigKey('DB_CHARSET'). '\');' . "\n" .
'  define(\'DB_SERVER\', \'' . $this->getConfigKey('DB_SERVER') . '\');' . "\n" .
'  define(\'DB_SERVER_USERNAME\', \'' . $this->getConfigKey('DB_SERVER_USERNAME') . '\');' . "\n" .
'  define(\'DB_SERVER_PASSWORD\', \'' . $this->getConfigKey('DB_SERVER_PASSWORD') . '\');' . "\n" .
'  define(\'DB_DATABASE\', \'' . $this->getConfigKey('DB_DATABASE') . '\');' . "\n\n" .
'  // The next 2 "defines" are for SQL cache support.' . "\n" .
'  // For SQL_CACHE_METHOD, you can select from:  none, database, or file' . "\n" .
'  // If you choose "file", then you need to set the DIR_FS_SQL_CACHE to a directory where your apache ' . "\n" .
'  // or webserver user has write privileges (chmod 666 or 777). We recommend using the "cache" folder inside the Zen Cart folder' . "\n" .
'  // ie: /path/to/your/webspace/public_html/zen/cache   -- leave no trailing slash  ' . "\n" .
'  define(\'SQL_CACHE_METHOD\', \'' . $this->getConfigKey('SQL_CACHE_METHOD') . '\'); ' . "\n" .
'  define(\'DIR_FS_SQL_CACHE\', \'' . $this->getConfigKey('DIR_FS_SQL_CACHE') . '\');' . "\n\n\n" .
'// Define the webserver and path parameters' . "\n" .
'  // Main webserver: eg-http://www.your_domain.com - ' . "\n" .
'  // HTTP_SERVER is your Main webserver: eg-http://www.your_domain.com' . "\n" .
'  // HTTPS_SERVER is your Secure webserver: eg-https://www.your_domain.com' . "\n" .
'  // HTTP_CATALOG_SERVER is your Main webserver: eg-http://www.your_domain.com' . "\n" .
'  // HTTPS_CATALOG_SERVER is your Secure webserver: eg-https://www.your_domain.com' . "\n" .
'  /* ' . "\n" .
'   * URLs for your site will be built via:  ' . "\n" .
'   *     HTTP_SERVER plus DIR_WS_ADMIN or' . "\n" .
'   *     HTTPS_SERVER plus DIR_WS_HTTPS_ADMIN or ' . "\n" .
'   *     HTTP_SERVER plus DIR_WS_CATALOG or ' . "\n" .
'   *     HTTPS_SERVER plus DIR_WS_HTTPS_CATALOG' . "\n" .
'   * ...depending on your system configuration settings' . "\n" .
'   */' . "\n" .
//'?'.'>' .
'// EOF';