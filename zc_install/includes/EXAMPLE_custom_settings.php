<?php
/**
 * Custom Settings used by the CLI version of zc_install
 * NOTE: Not Currently Used
 *
 * @package installer
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
$zc_settings = array();

/**
 * Custom settings for CLI installer can be set here
 */

/**
 * If you are building a 1-click install, set your vendor name in the installer_method setting.  This will be stamped in the generated configure.php files for reference and future troubleshooting.
 */
$zc_settings['installer_method'] = 'Automated Install';


/**
 * Set the domain name for accessing this store.
 */
$zc_settings['http_server_catalog'] = 'http://example.com';

/**
 * Set the SSL domain name to use for accessing this store. A VALID FUNCTIONAL SSL CERTIFICATE must be operational on the site.
 * NOTE: In the case of a shared-SSL certificate, enter the entire URL and designator needed to point to the document-root of the domain when in SSL mode.
 * ie: https://www.example.com  (in the case of a dedicated certificate)
 * ie: https://www.shared_site.com/~username
 * ie: https://www.shared_site.com/~username/example.com
 * ie: https://www.shared_site.com/www.example.com/secure
 */
$zc_settings['https_server_catalog'] = 'https://example.com';

/**
 * Set whether to tell Zen Cart to expect that SSL is active and fully functional on the site, and therefore to generate https URLs on relevant pages.
 * NOTE: If this is set to true and the https URL supplied above doesn't point to a site where SSL is already fully functional, the store will be broken.
 * So be sure to properly set up SSL on the domain before telling Zen Cart to use SSL.
 *
 * Accepted values:  'true' or 'false'.  Lowercase. Note these are strings, not booleans.
 */
$zc_settings['enable_ssl_catalog'] = 'true';

/**
 * Provide the URI path of the Zen Cart site relative to the Document-Root.
 * ie: '/' means the store is in the document-root. Most real sites will use this.
 * ie: '/store/' means the store is located in DOCUMENTROOT/store/ (ie: public_html/store)
 *
 *  Always start and end with a '/'.
 */
$zc_settings['dir_ws_http_catalog'] = '/';

/**
 * URI path to the document-root for the SSL domain.
 * IN MOST CASES THIS SHOULD BE THE SAME AS dir_ws_http_catalog
 *
 * Always start and end with a '/'.
 */
$zc_settings['dir_ws_https_catalog'] = '/';

/**
 * Provide the complete physical path to the store's files, as per the filesystem.
 * So, if the file system stores website files in /var/www/public_html/  then that's exactly what you'd enter here.
 * ie: /var/www/public_html/
 * ie: /home/users/username/public_html/
 * ie: /home/users/username/public_html/zen_cart
 */
$zc_settings['physical_path'] = '/var/www/public_html/according/to/your/webserver/configuration/';

/**
 * The only supported db_type at this time is 'mysql'. Don't change this.
 */
$zc_settings['db_type'] = 'mysql';

/**
 * The database "table-prefix" here is used to denote what "prefix" should be added to the beginning of all tablenames created and used in Zen Cart.
 * Ideally this should ALWAYS be set to an empty string, that is no prefix.
 * But in rare cases where users need to use prefixes to denote separate software systems because the host isn't willing to let users have multiple databases, then setting a prefix here might be useful.
 *
 * Note: using prefixes makes it harder for the end-user to do any self-administration or plugin installation. So best to leave this blank!
 */
$zc_settings['db_prefix'] = '';

/**
 * The character-set to use when talking to the database.
 * Possible values: 'utf8mb4' or older 'utf8' / 'latin1'
 * Default: 'utf8mb4'
 */
$zc_settings['db_charset'] = 'utf8mb4';

/**
 * Provide the internally-resolvable hostname to connect with the MySQL database.
 * Most hosts will use 'localhost' for this.
 * Some will require 172.0.0.1
 * Others may specify the name of another server hosting the database engine. In such cases be sure the network connection to that server is very fast, else the latency will slow the site to a crawl.
 */
$zc_settings['db_host'] = 'localhost';

/**
 * Provide the MySQL USERNAME that you've pre-generated for the domain
 * Of course, use only valid characters that MySQL will accept for a username.
 *
 * DO NOT USE 'root', as this would be non-compliant with PCI requirements.
 */
$zc_settings['db_user'] = '';

/**
 * Provide the Password that you've pre-generated and assigned to the MySQL user you've created.
 * Of course, use only valid characters that MySQL will accept for a username.
 */
$zc_settings['db_password'] = '';

/**
 * Provide the Name of the MySQL database which you've pregenerated for the domain.
 * NOTE: Be sure you've already assigned ALL PRIVILEGES to the MySQL user on this Database prior to installing Zen Cart.
 */
$zc_settings['db_name'] = '';

/**
 * Specify which SQL Cache method to use.
 * Default: 'none'
 * Other choices: 'database' or 'file'
 */
$zc_settings['sql_cache_method'] = 'none';

/**
 * Provide the Domain URL to use for accessing the Admin.
 * Ideally this will be the SAME as the https_catalog value. (Yes, the SSL URL, as long as SSL is fully functional on the domain already.)
 */
$zc_settings['http_server_admin'] = 'https://www.example.com';

/**
 * If you've already renamed the 'admin' folder to something else, specify that here.
 * PLEASE use a RANDOM string/word. Don't use the same value on every site, as that completely defeats the value of not using 'admin' as the literal value.
 */
$zc_settings['adminDir'] = 'admin';


