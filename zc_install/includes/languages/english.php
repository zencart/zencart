<?php
/**
 * Main English language file for installer
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Thu Aug 16 01:57:33 2012 -0400 Modified in v1.5.1 $
 */
/**
 * defining language components for the page
 */
  define('YES', 'YES');
  define('NO', 'NO');
  define('REFRESH_BUTTON', 'Re-Check');
  define('OKAY', 'Okay');

  // Global entries for the <html> tag
  define('HTML_PARAMS','dir="ltr" lang="en"');

  // charset for web pages and emails
  define('CHARSET', 'utf-8');

  // META TAG TITLE
  define('META_TAG_TITLE', (defined('TEXT_PAGE_HEADING') ? TEXT_PAGE_HEADING : 'Zen Cart&reg; Installer'));

  define('INSTALLATION_IN_PROGRESS','Installation In Progress...');

  if (isset($_GET['main_page']) && ($_GET['main_page']== 'index' || $_GET['main_page']== 'license')) {
    define('TEXT_ERROR_WARNING', 'Hi: Just a few issues that need addressing before we continue.');
  } else {
    define('TEXT_ERROR_WARNING', '<span class="errors"><strong>ATTENTION:&nbsp; Problems Found</strong></span>');
  }

  define('DB_ERROR_NOT_CONNECTED', 'Install Error: Could not connect to the Database');
  define('SHOULD_UPGRADE','You should consider upgrading!');
  define('MUST_UPGRADE','You need to upgrade this before installing Zen Cart&reg;');

  define('UPLOAD_SETTINGS','The Maximum upload size supported will be whichever the LOWER of these values:.<br />
<em>upload_max_filesize</em> in php.ini %s <br />
<em>post_max_size</em> in php.ini: %s <br />' .
//'<em>Zen Cart&reg;</em> Upload Setting: %s <br />' .
'You may find some Apache settings that prevent you from uploading files or limit your maximum file size.
See the Apache documentation for more information.');

  define('TEXT_HELP_LINK', ' more info...');
  define('TEXT_CLOSE_WINDOW', '[Close Window]');
  define('STORE_ADDRESS_DEFAULT_VALUE', 'Store Name
  Address
  Country
  Phone');

  define('ERROR_TEXT_ADMIN_CONFIGURE', '/admin/includes/configure.php does not exist');
  define('ERROR_CODE_ADMIN_CONFIGURE', '2');

  define('ERROR_TEXT_STORE_CONFIGURE', '/includes/configure.php file does not exist');
  define('ERROR_CODE_STORE_CONFIGURE', '3');

  define('ERROR_TEXT_PHYSICAL_PATH_ISEMPTY', 'Physical path is empty');
  define('ERROR_CODE_PHYSICAL_PATH_ISEMPTY', '9');

  define('ERROR_TEXT_PHYSICAL_PATH_INCORRECT', 'Physical path is incorrect');
  define('ERROR_CODE_PHYSICAL_PATH_INCORRECT', '10');

  define('ERROR_TEXT_VIRTUAL_HTTP_ISEMPTY', 'Virtual HTTP is empty');
  define('ERROR_CODE_VIRTUAL_HTTP_ISEMPTY', '11');

  define('ERROR_TEXT_VIRTUAL_HTTPS_ISEMPTY', 'Virtual HTTPS is empty');
  define('ERROR_CODE_VIRTUAL_HTTPS_ISEMPTY', '12');

  define('ERROR_TEXT_VIRTUAL_HTTPS_SERVER_ISEMPTY', 'Virtual HTTPS server is empty');
  define('ERROR_CODE_VIRTUAL_HTTPS_SERVER_ISEMPTY', '13');

  define('ERROR_TEXT_DB_USERNAME_ISEMPTY', 'Database UserName is empty');
  define('ERROR_CODE_DB_USERNAME_ISEMPTY', '16'); // re-using another one, since message is essentially the same.

  define('ERROR_TEXT_DB_HOST_ISEMPTY', 'Database Host is empty');
  define('ERROR_CODE_DB_HOST_ISEMPTY', '24');

  define('ERROR_TEXT_DB_NAME_ISEMPTY', 'Database Name is empty');
  define('ERROR_CODE_DB_NAME_ISEMPTY', '25');

  define('ERROR_TEXT_DB_SQL_NOTEXIST', 'SQL Install file does not exist');
  define('ERROR_CODE_DB_SQL_NOTEXIST', '26');

  define('ERROR_TEXT_DB_NOTSUPPORTED', 'Database not supported');
  define('ERROR_CODE_DB_NOTSUPPORTED', '27');

  define('ERROR_TEXT_DB_CONNECTION_FAILED', 'Connection to Database failed');
  define('ERROR_CODE_DB_CONNECTION_FAILED', '28');

  define('ERROR_TEXT_STORE_ZONE_NEEDS_SELECTION', 'Store Zone must be specified.');
  define('ERROR_CODE_STORE_ZONE_NEEDS_SELECTION', '29');

  define('ERROR_TEXT_DB_NOTEXIST', 'Database does not exist');
  define('ERROR_CODE_DB_NOTEXIST', '30');

  define('ERROR_TEXT_STORE_NAME_ISEMPTY', 'Store name is empty');
  define('ERROR_CODE_STORE_NAME_ISEMPTY', '31');

  define('ERROR_TEXT_STORE_OWNER_ISEMPTY', 'Store owner is empty');
  define('ERROR_CODE_STORE_OWNER_ISEMPTY', '32');

  define('ERROR_TEXT_STORE_OWNER_EMAIL_ISEMPTY', 'Store email address is empty');
  define('ERROR_CODE_STORE_OWNER_EMAIL_ISEMPTY', '33');

  define('ERROR_TEXT_STORE_OWNER_EMAIL_NOTEMAIL', 'Store email address is not valid');
  define('ERROR_CODE_STORE_OWNER_EMAIL_NOTEMAIL', '34');

define('ERROR_TEXT_STORE_ADDRESS_ISEMPTY', 'Store address is empty');
define('ERROR_CODE_STORE_ADDRESS_ISEMPTY', '35');

define('ERROR_TEXT_DEMO_SQL_NOTEXIST', 'Demo product SQL file does not exist');
define('ERROR_CODE_DEMO_SQL_NOTEXIST', '36');

define('ERROR_TEXT_ADMIN_USERNAME_ISEMPTY', 'Admin user name is empty');
define('ERROR_CODE_ADMIN_USERNAME_ISEMPTY', '46');

define('ERROR_TEXT_ADMIN_EMAIL_ISEMPTY', 'Admin email empty');
define('ERROR_CODE_ADMIN_EMAIL_ISEMPTY', '47');

define('ERROR_TEXT_ADMIN_EMAIL_NOTEMAIL', 'Admin email is not valid');
define('ERROR_CODE_ADMIN_EMAIL_NOTEMAIL', '48');

define('ERROR_TEXT_ADMIN_PASS_ISEMPTY', 'Admin password is empty');
define('ERROR_CODE_ADMIN_PASS_ISEMPTY', '49');

define('ERROR_TEXT_ADMIN_PASS_NOTEQUAL', 'Passwords do not match');
define('ERROR_CODE_ADMIN_PASS_NOTEQUAL', '50');

define('ERROR_TEXT_4_1_2', 'PHP Version is 4.1.2');
define('ERROR_CODE_4_1_2', '1');
define('ERROR_TEXT_PHP_OLD_VERSION', 'PHP Version not supported');
define('ERROR_CODE_PHP_OLD_VERSION', '55');
define('ERROR_TEXT_PHP_VERSION', 'PHP Version not supported');
define('ERROR_CODE_PHP_VERSION', '91');

define('ERROR_TEXT_ADMIN_CONFIGURE_WRITE', 'admin configure.php is not writeable');
define('ERROR_CODE_ADMIN_CONFIGURE_WRITE', '56');

define('ERROR_TEXT_STORE_CONFIGURE_WRITE', 'store configure.php is not writeable');
define('ERROR_CODE_STORE_CONFIGURE_WRITE', '57');

define('ERROR_TEXT_CACHE_DIR_ISEMPTY', 'The selected Session/SQL Cache Directory is empty');
define('ERROR_CODE_CACHE_DIR_ISEMPTY', '61');

define('ERROR_TEXT_CACHE_DIR_ISDIR', 'The selected Session/SQL Cache Directory does not exist');
define('ERROR_CODE_CACHE_DIR_ISDIR', '62');

define('ERROR_TEXT_CACHE_DIR_ISWRITEABLE', 'The selected Session/SQL Cache Directory is not writeable');
define('ERROR_CODE_CACHE_DIR_ISWRITEABLE', '63');

define('ERROR_TEXT_ADMIN_PASS_INSECURE', 'Password not secure enough. Requires letters and numbers, and at least 7 characters.');
define('ERROR_CODE_ADMIN_PASS_INSECURE', '64');

define('ERROR_TEXT_REGISTER_GLOBALS_ON', 'Register Globals is ON');
define('ERROR_CODE_REGISTER_GLOBALS_ON', '69');

define('ERROR_TEXT_SAFE_MODE_ON', 'Safe Mode is ON');
define('ERROR_CODE_SAFE_MODE_ON', '70');

define('ERROR_TEXT_CACHE_CUSTOM_NEEDED','Cache folder required to use file caching support');
define('ERROR_CODE_CACHE_CUSTOM_NEEDED', '71');

define('ERROR_TEXT_TABLE_RENAME_CONFIGUREPHP_FAILED','Could not update all your configure.php files with new prefix');
define('ERROR_CODE_TABLE_RENAME_CONFIGUREPHP_FAILED', '72');

define('ERROR_TEXT_TABLE_RENAME_INCOMPLETE','Could not rename all tables');
define('ERROR_CODE_TABLE_RENAME_INCOMPLETE', '73');

define('ERROR_TEXT_SESSION_SAVE_PATH','PHP "session.save_path" is not writable');
define('ERROR_CODE_SESSION_SAVE_PATH','74');

define('ERROR_TEXT_MAGIC_QUOTES_RUNTIME','PHP "magic_quotes_runtime" is active');
define('ERROR_CODE_MAGIC_QUOTES_RUNTIME','75');

define('ERROR_TEXT_DB_VER_UNKNOWN','Database Engine version information unknown');
define('ERROR_CODE_DB_VER_UNKNOWN','76');

define('ERROR_TEXT_UPLOADS_DISABLED','File Uploads are disabled');
define('ERROR_CODE_UPLOADS_DISABLED','77');

define('ERROR_TEXT_ADMIN_PWD_REQUIRED','Admin Password required to proceed with upgrade');
define('ERROR_CODE_ADMIN_PWD_REQUIRED','78');

define('ERROR_TEXT_PHP_SESSION_SUPPORT','PHP Session Support is required');
define('ERROR_CODE_PHP_SESSION_SUPPORT','80');

define('ERROR_TEXT_PHP_AS_CGI','PHP running as cgi not recommended unless server is Windows');
define('ERROR_CODE_PHP_AS_CGI','81');

define('ERROR_TEXT_DISABLE_FUNCTIONS','Required PHP functions are disabled on your server');
define('ERROR_CODE_DISABLE_FUNCTIONS','82');

define('ERROR_TEXT_OPENSSL_WARN','OpenSSL is "one" way in which a server can be configured to offer SSL (https://) support for your site.<br /><br />If this is showing as unavailable, possible causes could be:<br />(a) your webhost doesn\'t support SSL<br />(b) your webserver doesn\'t have OpenSSL installed, but MIGHT have another form of SSL services available<br />(c) your web host may not yet be aware of your SSL certificate details so that they can enable SSL support for your domain<br />(d) PHP may not be configured to know about OpenSSL yet.<br /><br />In any case, if you DO require encryption support on your web pages (SSL), you should be contacting your web hosting provider for assistance.');
define('ERROR_CODE_OPENSSL_WARN','79');

define('ERROR_TEXT_DB_PREFIX_NODOTS','Database Table-Prefix may only contain letters and numbers and underscores (_).');
define('ERROR_CODE_DB_PREFIX_NODOTS','83');

define('ERROR_TEXT_PHP_SESSION_AUTOSTART','PHP Session.autostart should be disabled.');
define('ERROR_CODE_PHP_SESSION_AUTOSTART','84');
define('ERROR_TEXT_PHP_SESSION_TRANS_SID','PHP Session.use_trans_sid should be disabled.');
define('ERROR_CODE_PHP_SESSION_TRANS_SID','86');
define('ERROR_TEXT_DB_PRIVS','Permissions Required for Database User');
define('ERROR_CODE_DB_PRIVS','87');
define('ERROR_TEXT_COULD_NOT_WRITE_CONFIGURE_FILES','Error encountered while writing /includes/configure.php');
define('ERROR_CODE_COULD_NOT_WRITE_CONFIGURE_FILES','88');
define('ERROR_TEXT_GD_SUPPORT','GD Support Details');
define('ERROR_CODE_GD_SUPPORT','89');

define('ERROR_TEXT_DB_MYSQL5','MySQL 5.7 (and higher) support not fully tested');
define('ERROR_CODE_DB_MYSQL5','90');

define('ERROR_TEXT_OPEN_BASEDIR','Could have problems uploading files or doing backups');
define('ERROR_CODE_OPEN_BASEDIR','92');
define('ERROR_TEXT_CURL_SUPPORT','CURL support not detected or found disabled');
define('ERROR_CODE_CURL_SUPPORT','93');
define('ERROR_TEXT_CURL_NOT_COMPILED', 'CURL not compiled into PHP - notify server administrator');
define('ERROR_TEXT_CURL_PROBLEM_GENERAL', 'CURL problems detected: ');
define('ERROR_TEXT_CURL_SSL_PROBLEM', 'CURL requires SSL support. Please notify webmaster or hosting company.');
define('ERROR_CODE_CURL_SSL_PROBLEM','95');

define('ERROR_TEXT_MAGIC_QUOTES_SYBASE','PHP "magic_quotes_sybase" is active');
define('ERROR_CODE_MAGIC_QUOTES_SYBASE','94');

$error_code ='';
if (isset($_GET['error_code'])) {
  $error_code = $_GET['error_code'];
  }

switch ($error_code) {
  case ('1'):
    define('POPUP_ERROR_HEADING', 'PHP Version 4.1.2 Detected');
    define('POPUP_ERROR_TEXT', 'Some releases of PHP Version 4.1.2 have a bug which affects super global arrays. This may result in the admin section of Zen Cart&reg; not being accessible. You are advised to upgrade your PHP version if possible.<br /><br />PHP 4.3.2 or greater is the minimum requirement for Zen Cart&reg;.<br />We STRONGLY recommend using PHP 4.3.11 or greater (in the v4.x series).');

  break;
  case ('2'):
    define('POPUP_ERROR_HEADING', '/admin/includes/configure.php does not exist');
    define('POPUP_ERROR_TEXT', 'The file /admin/includes/configure.php does not exist. You can create this either as a blank file or by renaming /admin/includes/dist-configure.php to configure.php.  After creating it, you need to mark it read-write or CHMOD 666 or maybe 755 or 777 depending on your webserver configuration (ask your hosting company).');

  break;
  case ('3'):
    define('POPUP_ERROR_HEADING', '/includes/configure.php does not exist');
    define('POPUP_ERROR_TEXT', 'The file /includes/configure.php does not exist. You can create this either as a blank file or by renaming /includes/dist-configure.php to configure.php.  After creating it, you need to mark it read-write or CHMOD 666 or maybe 755 or 777 depending on your webserver configuration (ask your hosting company).');

  break;
  case ('4'):
    define('POPUP_ERROR_HEADING', 'Physical Path');
    define('POPUP_ERROR_TEXT', 'The physiscal path is the path to the directory where your Zen Cart&reg; files are installed. For example on some linux systems the HTML files are stored in /var/www/html. If you then put your Zen Cart&reg; files in a directory called \'store\', the physical path would be /var/www/html/store. The installer usually can be trusted to guess this directory correctly.');

  break;
  case ('5'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTP Path');
    define('POPUP_ERROR_TEXT', 'This is the address you would need to put into a web browser to view your Zen Cart&reg; website. If the site is in the \'root\' of your domain, this would be \'http://www.yourdomain.com\'. If you had put the files under a directory called \'store\' then the path would be \'http://www.yourdomain.com/store\'.');

  break;
  case ('6'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTPS Server');
    define('POPUP_ERROR_TEXT', 'This is the web server address for your secure/SSL server. This address varies depending on how SSL/Secure mode is implemented on your server. You are advised to read the <a href="http://tutorials.zen-cart.com/index.php?article=14" target="_blank">FAQ Entry</a> on SSL to ensure this is set correctly.');

  break;
  case ('7'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTPS Path');
    define('POPUP_ERROR_TEXT', 'This is the address you would need to put into a web browser to view your Zen Cart&reg; website in secure/SSL mode. You are advised to read the <a href="http://tutorials.zen-cart.com/index.php?article=14" target="_blank">FAQ Entry</a> on SSL to ensure this is set correctly.');

  break;
  case ('8'):
    define('POPUP_ERROR_HEADING', 'Enable SSL');
    define('POPUP_ERROR_TEXT', 'This setting determines whether SSL/Secure (HTTPS:) mode is used on security-vulnerable pages of your Zen Cart&reg; website.<br /><br />Any page where personal information is entered e.g. login, checkout, account details can be protected by SSL/Secure mode.  It can also be actived for the Administration area.<br /><br />You must have access to an SSL server (denoted by using HTTPS instead of HTTP). <br /><br />IF YOU ARE NOT SURE if you have an SSL server then please leave this setting set to NO for now, and check with your hosting provider. Note: As with all settings, this can be changed later by editing the appropriate configure.php file.');

  break;
  case ('9'):
    define('POPUP_ERROR_HEADING', 'Physical Path is empty');
    define('POPUP_ERROR_TEXT', 'You have left the entry for the Physical path empty. You must make a valid entry here.');

  break;
  case ('10'):
    define('POPUP_ERROR_HEADING', 'Physical Path is incorrect');
    define('POPUP_ERROR_TEXT', 'The entry you have made for the Physical Path does not appear to be valid. Please correct and try again.<br />Zen Cart&reg; is looking for its files in this path, including attempting to verify that an index.php file is present there. If it is missing, then you probably have one or more failures during your FTP upload, and should double-check everything to fix whatever was missed.');

  break;
  case ('11'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTP is empty');
    define('POPUP_ERROR_TEXT', 'You have left the entry for the Virtual HTTP path empty. You must make a valid entry here.');

  break;
  case ('12'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTPS is empty');
    define('POPUP_ERROR_TEXT', 'You have left the entry for the Virtual HTTPS path empty as well as enabling SSL mode. You must make a valid entry here or disable SSL mode.');

  break;
  case ('13'):
    define('POPUP_ERROR_HEADING', 'Virtual HTTPS server is empty');
    define('POPUP_ERROR_TEXT', 'You have left the entry for the Virtual HTTPS server empty as well as enabling SSL mode. You must make a valid entry here or disable SSL mode');

  break;
  case ('14'):
    define('POPUP_ERROR_HEADING', 'Database Character Set / Collation');
    define('POPUP_ERROR_TEXT', 'By default, Zen Cart&reg; uses the utf8 character set / collation for the database and files.');

  break;
  case ('15'):
    define('POPUP_ERROR_HEADING', 'Database Host');
    define('POPUP_ERROR_TEXT', 'This is the name of the webserver on which your host runs their database program. In most cases this can always be left set to \'localhost\'. In some exceptional cases you will need to ask your hosting provider for the server name of their database server.<br /><br />Most hosts use "localhost"<br />Yahoo Hosting always uses "mysql"<br />Other hosts will tell you the host-name via your control panel.');

  break;
  case ('16'):
    define('POPUP_ERROR_HEADING', 'Database User Name');
    define('POPUP_ERROR_TEXT', 'All databases require a username and password to access them. The username for your database may well have been assigned by your hosting provider and you should contact them for details.<br /><br />Sometimes the name is prefixed by your webhosting account name, followed by the database-user-name you chose. ie: myaccount_zencartuser');

  break;
  case ('17'):
    define('POPUP_ERROR_HEADING', 'Database Password');
    define('POPUP_ERROR_TEXT', 'All databases require a username and password to access them. The password for your database may well have been assigned by your hosting provider and you should contact them for details.<br /><br />Remember that the password is case-sensitive.');

  break;
  case ('18'):
    define('POPUP_ERROR_HEADING', 'Database Name');
    define('POPUP_ERROR_TEXT', 'This is the name of the database that will be used for Zen Cart&reg;. If you are unsure as to what this should be, then you should contact your hosting provider for more information.<br /><br />Sometimes the name is prefixed by your webhosting account name, followed by the database-name you chose. ie: myaccount_zencartdb');

  break;
  case ('19'):
    define('POPUP_ERROR_HEADING', 'Database Table-Prefix');
    define('POPUP_ERROR_TEXT', 'Zen Cart&reg; allows you to add a prefix to the table names it uses to store its information. This is especially useful if your host only allows you one database, and you want to install other scripts on your system that share the same database, by causing the Zen Cart&reg; tables to be easily identified because of the table-prefix. <br /><br /><strong>Normally you should just leave the default setting as-is (ie: blank).</strong><br /><br />Valid characters include: numbers and letters and underscores (_).');

  break;
  case ('20'):
    define('POPUP_ERROR_HEADING', 'Database Create');
    define('POPUP_ERROR_TEXT', 'This setting determines whether the installer should attempt to create the main database for Zen Cart&reg;. Note \'create\' in this context has nothing to do with adding the tables that Zen Cart&reg; needs, which will be done automatically anyway. Many hosts will not give their users \'create\' permissions, but provide another method for creating blank databases, e.g. cPanel or phpMyAdmin.');

  break;
//  case ('21'):
//    define('POPUP_ERROR_HEADING', 'Database Connection');
//    define('POPUP_ERROR_TEXT', 'Persistent connections are a method of reducing the load on the database. You should consult your server host before setting this option.  Enabling "persistent connections" could cause your host to experience database problems if they haven\'t configured to handle it.<br /><br />Again, be sure to talk to your host before considering use of persistent connections.');
//
//  break;
//  case ('22'):
//    define('POPUP_ERROR_HEADING', 'Database Sessions');
//    define('POPUP_ERROR_TEXT', 'This detemines whether session information is stored in a file or in the database. While file-based sessions are slightly faster, <strong>database sessions are recommended</strong> for all online stores using SSL connections, for the sake of security.');
//
//  break;
  case ('23'):
    define('POPUP_ERROR_HEADING', 'Enable SSL');
    define('POPUP_ERROR_TEXT', 'Setting this to "true" simply turns on the switch that causes Zen Cart&reg; to ATTEMPT to operate certain pages in SSL mode.  Successful operation depends on you entering the correct HTTPS servername and path information. Your hosting provider should supply this information to you.<br />If you do not already have SSL support, you may have to purchase it. This includes a monthly charge for a dedicated IP address as well as an annual fee for the SSL certificate.');

  break;
  case ('24'):
    define('POPUP_ERROR_HEADING', 'Database Host is empty');
    define('POPUP_ERROR_TEXT', 'The entry for Database Host is empty. Please enter a valid Database Server Hostname. <br />This is the name of the webserver on which your host runs their database program. In most cases this can always be left set to \'localhost\'. In some exceptional cases you will need to ask your hosting provider for the server name of their database server.');
  break;

  case ('25'):
    define('POPUP_ERROR_HEADING', 'Database Name is empty');
    define('POPUP_ERROR_TEXT', 'The entry for Database Name is empty. Please enter the name of the database you wish to use for Zen Cart&reg;.<br />This is the name of the database that will be used for Zen Cart&reg;. If you are unsure as to what this should be, then you should contact your hosting provider for more information.');

  break;
  case ('26'):
    define('POPUP_ERROR_HEADING', 'SQL Install file does not exist');
    define('POPUP_ERROR_TEXT', 'The installer could not find the required .SQL install file. This should exist within the \'zc_install/sql\' directory and be called something like \'mysql_zencart.sql\'.');

  break;
  case ('27'):
    define('POPUP_ERROR_HEADING', 'Database not supported');
    define('POPUP_ERROR_TEXT', 'The database type you have selected does not appear to be supported by the PHP version you have installed. You may need to check with your hosting provider to check that the database type you have selected is supported. If this is your own server, then please ensure that support for the database type has been compiled into PHP, and that the necessary extensions/modules/dll files are being loaded (esp check php.ini for extension=mysql.so, etc).');

  break;
  case ('28'):
    define('POPUP_ERROR_HEADING', 'Connection to Database failed');
    define('POPUP_ERROR_TEXT', 'A connection to the database could not be made. This can happen for a number of reasons. <br /><br />
You may have given the wrong DB host name, or the user name or <em>password </em>may be incorrect. <br /><br />
You may also have given the wrong database name (<strong>Does it exist?</strong> <strong>Did you create it?</strong> -- NOTE: Zen Cart&reg; does not create a database for you.).<br /><br />Please review all of the entries and ensure that they are correct.');

  break;
  case ('29'):
    define('POPUP_ERROR_HEADING', 'Store Zone must be seleected');
    define('POPUP_ERROR_TEXT', 'Please select a zone from the Store Zones list. This information is used for tax and shipping calculations. You can always change it at a later date via Admin->Configuration->My Store.');

  break;
  case ('30'):
    define('POPUP_ERROR_HEADING', 'Database does not exist');
    define('POPUP_ERROR_TEXT', 'The database name you have specified does not appear to exist.<br />(<strong>Did you create it?</strong> -- NOTE: Zen Cart&reg; does not create a database for you.).<br /><br />Please check your database details, then verify this entry and make corrections where necessary.<br /><br />You may need to use your webhosting control panel to create the database. While creating it, make note of the username and password, as well as the database-name used, as you will need this information to fill in the details on this installer screen.');

  break;
  case ('31'):
    define('POPUP_ERROR_HEADING', 'Store name is empty');
    define('POPUP_ERROR_TEXT', 'Please specify the name by which you will refer to your store.');

  break;
  case ('32'):
    define('POPUP_ERROR_HEADING', 'Store owner is empty');
    define('POPUP_ERROR_TEXT', 'Please supply the name of the store owner.  This information will appear in the \'Contact Us\' page, the \'Welcome\' email messages, and other places throughout the store.');

  break;
  case ('33'):
    define('POPUP_ERROR_HEADING', 'Store email address is empty');
    define('POPUP_ERROR_TEXT', 'Please supply the store\'s primary email address. This is the address which will be supplied for contact information in emails that are sent out from the store. It will not be displayed on any pages in the store unless you manually do such configuration.');

  break;
  case ('34'):
    define('POPUP_ERROR_HEADING', 'Store email address is not valid');
    define('POPUP_ERROR_TEXT', 'You must supply a valid email address.');

  break;
  case ('35'):
    define('POPUP_ERROR_HEADING', 'Store address is empty');
    define('POPUP_ERROR_TEXT', 'Please supply the street address of your store.  This will be displayed on the Contact-Us page (this can be disabled if required), and on invoice/packing-slip materials. It will also be displayed if a customer elects to purchase by check/money-order, upon checkout.');

  break;
  case ('36'):
    define('POPUP_ERROR_HEADING', 'Demo product SQL file does not exist');
    define('POPUP_ERROR_TEXT', 'We were unable to locate the SQL file containing the Zen Cart&reg; demo products to load them into your store.  Please check that the /zc_install/demo/xxxxxxx_demo.sql file exists. (xxxxxxx = your database-type).');

  break;
  case ('37'):
    define('POPUP_ERROR_HEADING', 'Store Name');
    define('POPUP_ERROR_TEXT', 'The name of your store. This will be used in emails sent by the system and in some cases, the browser title.');

  break;
  case ('38'):
    define('POPUP_ERROR_HEADING', 'Store Owner');
    define('POPUP_ERROR_TEXT', 'The Store Owner details may be used in emails sent by the system.');

  break;
  case ('39'):
    define('POPUP_ERROR_HEADING', 'Store Owner Email');
    define('POPUP_ERROR_TEXT', 'The main email address by which your store can be contacted. Most emails sent by the system will use this, as well as contact us pages.');

  break;
  case ('40'):
    define('POPUP_ERROR_HEADING', 'Store Country');
    define('POPUP_ERROR_TEXT', 'The country your store is based in. It is important that you set this correctly to ensure that Tax and shipping options work correctly.  It also determines the address-label layout on invoicing, etc.');

  break;
  case ('41'):
    define('POPUP_ERROR_HEADING', 'Store Zone');
    define('POPUP_ERROR_TEXT', 'This represents a geographical sub-division of the country your store is based in. eg. A state in the U.S.A.');

  break;
  case ('42'):
    define('POPUP_ERROR_HEADING', 'Store Address');
    define('POPUP_ERROR_TEXT', 'Your Store Address, used on invoices and order confirmations');

  break;
  case ('43'):
    define('POPUP_ERROR_HEADING', 'Store Default Language');
    define('POPUP_ERROR_TEXT', 'The default language your store will use. Zen Cart&reg; is inherently multi-language, provided the correct language pack is loaded. At the moment Zen Cart&reg; only comes with an English Language Pack as default.  Others, donated by Zen Cart&reg; community members, can be obtained from the downloads area of the www.zen-cart.com website.');

  break;
  case ('44'):
    define('POPUP_ERROR_HEADING', 'Store Default Currency');
    define('POPUP_ERROR_TEXT', 'Select a default currency which your store will operate on.  If your desired currency is not listed here, it can be changed easily in the Admin area after installation is complete.');

  break;
  case ('45'):
    define('POPUP_ERROR_HEADING', 'Install Demo Products');
    define('POPUP_ERROR_TEXT', 'Please select whether you wish to install the demo products into the database in order to preview the methods by which various features of Zen Cart&reg; operate.');

  break;
  case ('46'):
    define('POPUP_ERROR_HEADING', 'Admin user name is empty');
    define('POPUP_ERROR_TEXT', 'To log into the Admin area after install is complete, you need to supply an Admin username here.');

  break;
  case ('47'):
    define('POPUP_ERROR_HEADING', 'Admin email empty');
    define('POPUP_ERROR_TEXT', 'The Admin email address is required in order to send password-resets in case you forget the password.');

  break;
  case ('48'):
    define('POPUP_ERROR_HEADING', 'Admin email is not valid');
    define('POPUP_ERROR_TEXT', 'Please supply a valid email address.');

  break;
  case ('49'):
    define('POPUP_ERROR_HEADING', 'Admin password is empty');
    define('POPUP_ERROR_TEXT', 'For security, the Administrator\'s password cannot be blank.');

  break;
  case ('50'):
    define('POPUP_ERROR_HEADING', 'Passwords do not match');
    define('POPUP_ERROR_TEXT', 'Please re-enter the administrator password and confirmation password.');

  break;
  case ('51'):
    define('POPUP_ERROR_HEADING', 'Admin User Name');
    define('POPUP_ERROR_TEXT', 'To log into the Admin area after install is complete, you need to supply an Admin username here.');

  break;
  case ('52'):
    define('POPUP_ERROR_HEADING', 'Admin Email Address');
    define('POPUP_ERROR_TEXT', 'The Admin email address is required in order to send password-resets in case you forget the password.');

  break;
  case ('53'):
    define('POPUP_ERROR_HEADING', 'Admin Password');
    define('POPUP_ERROR_TEXT', 'The administrator password is your secure password to allow you access to the administration area.  It must contain both letters and numbers, and a minimum length of at least 7 characters.');

  break;
  case ('54'):
    define('POPUP_ERROR_HEADING', 'Admin Password Confirmation');
    define('POPUP_ERROR_TEXT', 'Naturally, you need to supply matching passwords before the password can be saved for future use.');

  break;
  case ('55'):
    define('POPUP_ERROR_HEADING', 'PHP Version not supported');
    define('POPUP_ERROR_TEXT', 'The PHP Version running on your webserver is not supported by Zen Cart&reg;. <br /><br />PHP 5.3.14 is the minimum requirement. <br />However, we recommend that you use at least PHP v5.3.3 or higher if possible.<br /><br />If you are trying to use older PHP versions, note that using older releases of PHP may result in the admin section of Zen Cart&reg; not being accessible, might leave your site vulnerable to hacking, and may not support some of the PHP Session code that handles keeping individual customer logins unique and separate from other customers. You are advised to upgrade your PHP version.');

  break;
  case ('56'):
    define('POPUP_ERROR_HEADING', 'Admin configure.php is not writeable');
    define('POPUP_ERROR_TEXT', '<em><strong>Related FAQs:</strong></em><br /><a href="http://tutorials.zen-cart.com/index.php?article=9" target="_blank">How do I set permissions on files?</a><br /><a href="http://tutorials.zen-cart.com/index.php?article=148" target="_blank">What is CHMOD and what do the numbers mean?</a><br /><a href="http://tutorials.zen-cart.com/index.php?article=107#configurephp" target="_blank">How do I set permissions for configure.php files for installation?</a><br /><br />The file <strong>admin/includes/configure.php</strong> is not writeable.<br /><br />If you are using a Unix or Linux system then please CHMOD the file to 777 or 666 until the Zen Cart&reg; install is completed.  This can usually be done by way of your FTP program (right-click or edit file properties, etc).<br /><br />On a Windows desktop system it may be simply enough that the file is set to read/write.<br /><br />On a Windows Server, especially if running under IIS, you will have to right-click on the file, click on Security, and ensure that the "Internet Guest Account" or IUSR_xxxxxxx user has read and write access.<br /><br /><strong>Once installation is complete,</strong> you should set the file back to read-only again (CHMOD 644 or 444, or in Windows, uncheck the "write" options, or "check" the read-only box).');

  break;
  case ('57'):
    define('POPUP_ERROR_HEADING', 'Store configure.php is not writeable');
    define('POPUP_ERROR_TEXT', '<em><strong>Related FAQs:</strong></em><br /><a href="http://tutorials.zen-cart.com/index.php?article=9" target="_blank">How do I set permissions on files?</a><br /><a href="http://tutorials.zen-cart.com/index.php?article=148" target="_blank">What is CHMOD and what do the numbers mean?</a><br /><a href="http://tutorials.zen-cart.com/index.php?article=107#configurephp" target="_blank">How do I set permissions for configure.php files for installation?</a><br /><br />The file <strong>includes/configure.php</strong> is not writeable. If you are using a Unix or Linux system then please CHMOD the file to 777 or 666 until the Zen Cart&reg; install is completed.  This can usually be done by way of your FTP program (right-click or edit file properties, etc).<br /><br />On a Windows desktop system it may be simply enough that the file is set to read/write.<br /><br />On a Windows Server, especially if running under IIS, you will have to right-click on the file, click on Security, and ensure that the "Internet Guest Account" or IUSR_xxxxxxx user has read and write access.<br /><br /><strong>Once installation is complete,</strong> you should set the file back to read-only again (CHMOD 644 or 444, or in Windows, uncheck the "write" options, or "check" the read-only box).');

  break;
  case ('58'):
    define('POPUP_ERROR_HEADING', 'DB Table Prefix');
    define('POPUP_ERROR_TEXT', 'Zen Cart&reg; allows you to add a prefix to the table names it uses to store its information. This is especially useful if your host only allows you one database, and you want to install other scripts on your system that use that database. <strong>Normally you should just leave the setting blank.</strong>');

  break;
  case ('59'):
    define('POPUP_ERROR_HEADING', 'SQL Cache Directory');
    define('POPUP_ERROR_TEXT', 'SQL queries can be cached either in the database, in a file on your server\'s hard disk, or not at all. If you choose to cache SQL queries to a file on your server\'s hard disk, then you must provide the directory where this information can be saved. <br /><br />The standard Zen Cart&reg; installation includes a \'cache\' folder.  You need to mark this folder read-write for your webserver (ie: apache) to access it.<br /><br />Please ensure that the directory you select exists and is writeable by the web server (CHMOD 777 or at least 666 recommended).');

  break;
  case ('60'):
    define('POPUP_ERROR_HEADING', 'SQL Cache Method');
    define('POPUP_ERROR_TEXT', 'Some SQL queries are marked as being cacheable. This means that if they are cached they will run much more quickly. You can decide which method is used to cache the SQL Query.<br /><br /><strong>None</strong>. SQL queries are not cached at all. If you have very few products/categories you might actually find this gives the best speed for your site.<br /><br /><strong>Database</strong>. SQL queries are cached to a database table. Sounds strange but this might provide a speed increase for sites with medium numbers of products/categories.<br /><br /><strong>File</strong>. SQL Queries are cached to your server\'s hard disk. For this to work you must ensure that the directory where queries are cached to is writeable by the web server. This method is probably most suitable for sites with a large number of products/categories.');

  break;
  case ('61'):
    define('POPUP_ERROR_HEADING', 'The Session/SQL Cache Directory entry is empty');
    define('POPUP_ERROR_TEXT', 'If you wish to use file caching for Session/SQL queries, you must supply a valid directory on your webserver, and ensure that the webserver has rights to write into that folder/directory.');

  break;
  case ('62'):
    define('POPUP_ERROR_HEADING', 'The Session/SQL Cache Directory entry does not exist');
    define('POPUP_ERROR_TEXT', 'If you wish to use file caching for Session/SQL queries, you must supply a valid directory on your webserver, and ensure that the webserver has rights to write into that folder/directory.');

  break;
  case ('63'):
    define('POPUP_ERROR_HEADING', 'The Session/SQL Cache Directory entry is not writeable');
    define('POPUP_ERROR_TEXT', 'If you wish to use file caching for Session/SQL queries, you must supply a valid directory on your webserver, and ensure that the webserver has rights to write into that folder/directory.  CHMOD 666 or 777 is advisable under Linux/Unix.  Read/Write is suitable under Windows servers (in IIS, must set this for the Internet Guest Account).');
  break;

  case ('64'):
    define('POPUP_ERROR_HEADING', 'Admin Password Security Requirements');
    define('POPUP_ERROR_TEXT', 'Your admin password must be a minimum of 7 characters, and must contain both letters and numbers.<br /><br />Note: Passwords will expire at least every 90 days.');

  break;
//  case ('65'):
//    define('POPUP_ERROR_HEADING', 'phpBB Database Table-Prefix');
//    define('POPUP_ERROR_TEXT', 'Please supply the table-prefix for your phpBB tables in the database where they are located. This is usually \'phpBB_\'');
//
//  break;
//  case ('66'):
//    define('POPUP_ERROR_HEADING', 'phpBB Database Name');
//    define('POPUP_ERROR_TEXT', 'Please supply the database name where your phpBB tables are located.');
//  break;
//  case ('67'):
//    define('POPUP_ERROR_HEADING', 'phpBB Directory');
//    define('POPUP_ERROR_TEXT', 'Please supply the full/complete path to where your phpBB script files are stored. This will allow Zen Cart&reg; to know what path to direct users to when they click on the phpBB link in your store.<br /><br />The path entered here is relative to the "root" of your server. So, if your phpBB installation is in <strong>/home/users/username/public_html/phpbb </strong>, then you need to enter <strong>/home/users/username/public_html/phpbb/ </strong>here. If it is under another set of subfolders, you need to list those folders in the path.<br /><br />We will look to find your "<em>config.php</em>" file in that folder.');
//  break;
//  case ('68'):
//    define('POPUP_ERROR_HEADING', 'phpBB Directory');
//    define('POPUP_ERROR_TEXT', 'No phpBB configure file could be found in the directory you specified. You must already have installed phpBB if you wish to use this automatic configuration. Otherwise you will have to skip automatic phpBB configuration and set it up manually later.<br /><br />The path entered here is relative to the "root" of your server. So, if your phpBB installation is in <strong>/home/users/username/public_html/phpbb </strong>, then you need to enter <strong>/home/users/username/public_html/phpbb/ </strong>here. If it is under another set of subfolders, you need to list those folders in the path.<br /><br />We will look to find your "<em>config.php</em>" file in that folder.');
//  break;
  case ('69'):
    define('POPUP_ERROR_HEADING', 'Register Globals');
    define('POPUP_ERROR_TEXT', 'Zen Cart&reg; can work with the "Register Globals" setting on or off.  However, having it "off" leaves your system somewhat more secure.<br /><br />If you wish to disable it, and your hosting company won\'t turn it off for you, you might try adding this to an .htaccess file in the root of your shop (you may have to create the file if you don\'t already have one):<br /><br /><code>php_value session.use_trans_sid off<BR />php_value register_globals off<br />#php_value register_globals off<BR />&lt;Files ".ht*"&gt;<BR />deny from all<BR />&lt;/Files&gt;</code><br /><br />or talk to your hosting company for assistance.');
  break;
  case ('70'):
    define('POPUP_ERROR_HEADING', 'Safe Mode is On');
    define('POPUP_ERROR_TEXT', 'Zen Cart&reg;, being a full-service e-Commerce application, does not work well on servers running in Safe Mode.<br /><br />To run an e-Commerce system requires many advanced services often restricted on lower-cost "shared" hosting services. To run your online store in optimum fashion will require setting up a webhosting service that does not place you or your webspace in "Safe Mode".  You need your hosting company to set "SAFE_MODE=OFF" in your php.ini file.');
  break;
  case ('71'):
    define('POPUP_ERROR_HEADING', 'Cache folder required to use file-based caching support');
    define('POPUP_ERROR_TEXT', 'If you wish to use the "file-based SQL cache support" in Zen Cart&reg;, you\'ll need to set the proper permissions on the cache folder in your webspace.<br /><br />Optionally, you can choose "Database Caching" or "No Caching" if you prefer not to use the cache folder. In this case, you MAY need to disable "store sessions" as well, as the session tracker uses the file cache as well.<br /><br />To set up the cache folder properly, use your FTP program or shell access to your server to CHMOD the folder to 666 or 777 read-write permissions level.<br /><br />Most specifically, the userID of your webserver (ie: \'apache\' or \'www-user\' or maybe \'IUSR_something\' under Windows) must have all \'read-write-delete\' etc privileges to the cache folder.');
  break;
  case ('72'):
    define('POPUP_ERROR_HEADING', 'ERROR: Could not update all your configure.php files with new prefix');
    define('POPUP_ERROR_TEXT', 'While attempting to update your configure.php files after renaming tables, we encountered an error.  You will need to manually edit your /includes/configure.php and /admin/includes/configure.php files and ensure that the "define" for "DB_PREFIX" is set properly for your Zen Cart&reg; tables in your database.');
  break;
  case ('73'):
    define('POPUP_ERROR_HEADING', 'ERROR: Could not apply new table-prefix to all tables');
    define('POPUP_ERROR_TEXT', 'While attempting to rename your database tables with the new table prefix, we encountered an error.  You will need to manually review your database tablenames for accuracy. Worst-case, you may need to recover from your backup.');
  break;
  case ('74'):
    define('POPUP_ERROR_HEADING', 'NOTE: PHP "session.save_path" is not writable');
    define('POPUP_ERROR_TEXT', '<strong>This is JUST a note </strong>to inform you that you do not have permission to write to the path specified in the PHP session.save_path setting.<br /><br />This simply means that you cannot use this path setting for temporary file storage.  Instead, use the "suggested cache path" shown below it.<br /><br /><br />Alternatively, if the path is unknown, then it\'s possible that this setting is not set in your server\'s php.ini settings. This is not a problem. It is primarily just a status alert. Talk to your webhost for further clarification if you so desire.');
  break;
  case ('75'):
    define('POPUP_ERROR_HEADING', 'NOTE: PHP "magic_quotes_runtime" is active');
    define('POPUP_ERROR_TEXT', 'It is required to have "magic_quotes_runtime" disabled. When enabled, it can cause unexpected 1064 SQL errors, and other code-execution problems.<br /><br />If you cannot disable it for the whole server, it may be possible to disable via .htaccess or your own php.ini file in your private webspace.  Talk to your hosting company for assistance.');
  break;
  case ('76'):
    define('POPUP_ERROR_HEADING', 'Database Engine version information unknown');
    define('POPUP_ERROR_TEXT', 'The version number of your database engine could not be obtained.<br /><br />This is NOT NECESSARILY a serious issue. In fact, it can be quite common on a production server, as at the stage of this inspection, we may not yet know the required security credentials in order to log in to your server, since those are obtained later in the installation process.<br /><br />It is generally safe to proceed even if this information is listed as Unknown.');
  break;
  case ('77'):
    define('POPUP_ERROR_HEADING', 'File Uploads are DISABLED');
    define('POPUP_ERROR_TEXT', 'File uploads are DISABLED. To enable them, make sure <em><strong>file_uploads = on</strong></em> is in your server\'s php.ini file.');
  break;
  case ('78'):
    define('POPUP_ERROR_HEADING', 'ADMIN PASSWORD REQUIRED TO UPGRADE');
    define('POPUP_ERROR_TEXT', 'The Store Administrator username and password are required in order to make changes to the database.<br /><br />Please enter a valid admin user ID and password for your Zen Cart&reg; site.');
  break;
  case ('79'):
    define('POPUP_ERROR_TEXT','OpenSSL is "one" way in which a server can be configured to offer SSL (https://) support for your site.<br /><br />If this is showing as unavailable, possible causes could be:<br />(a) your webhost doesn\'t support SSL<br />(b) your webserver doesn\'t have OpenSSL installed, but MIGHT have another form of SSL services available<br />(c) your web host may not yet be aware of your SSL certificate details so that they can enable SSL support for your domain<br />(d) PHP may not be configured to know about OpenSSL yet.<br /><br />In any case, if you DO require encryption support on your web pages (SSL), you should be contacting your web hosting provider for assistance.');
    define('POPUP_ERROR_HEADING','OpenSSL Information');
  break;
  case ('80'):
    define('POPUP_ERROR_HEADING', 'PHP Session Support is Required');
    define('POPUP_ERROR_TEXT', 'You need to enable PHP Session support on your webserver.  You might try installing this module: php4-session<br /><br /><br />PHP Session Support is required in order to support user-login and payment/checkout procedures. Please talk to your host to reconfigure PHP to enable session support.');
  break;
  case ('81'):
    define('POPUP_ERROR_HEADING', 'PHP running as cgi not recommended unless server is Windows');
    define('POPUP_ERROR_TEXT', 'Running PHP as CGI can be problematic on some Linux/Unix servers.<br /><br />Windows servers, however, "always" run PHP as a cgi module, in which case this warning can be ignored.');
  break;
  case ('82'):
    define('POPUP_ERROR_HEADING', ERROR_TEXT_DISABLE_FUNCTIONS);
    define('POPUP_ERROR_TEXT', 'Your PHP configuration has one or more of the following functions marked as "disabled" in your server\'s PHP.INI file:<br /><ul><li>set_time_limit</li><li>exec</li></ul>Your server may suffer from decreased performance due to the use of these security measures which are usually implemented on highly-used public servers... which are not always ideal for running an e-Commerce system.<br /><br />It is recommended that you speak with your hosting provider to determine whether they have another server where you may run your site with these restrictions removed.');
  break;
  case ('83'):
    define('POPUP_ERROR_HEADING','Invalid characters in database table-prefix');
    define('POPUP_ERROR_TEXT','The database Table-Prefix must consist only of letters, numbers, and underscores (_). <br /><br />Please select a different prefix. <strong>We recommend leaving it blank</strong> or using something simple like "zen_" .');
  break;
  case ('84'):
    define('POPUP_ERROR_HEADING','PHP Session.autostart should be disabled.');
    define('POPUP_ERROR_TEXT','The session.auto_start setting in your server\'s PHP.INI file is set to ON. <br /><br />This could potentially cause you some problems with session handling, as Zen Cart&reg; is designed to start sessions when it\'s ready to activate session features. Having sessions start automatically can be a problem in some server configurations.<br /><br />If you wish to attempt disabling this yourself, you could try putting the following into a .htaccess file located in the root of your shop (same folder as index.php):<br /><br /><code>php_value session.auto_start 0</code><br /><br /> (You may have to create the file if you don\'t already have one.)');
  break;
  case ('85'):
    define('POPUP_ERROR_HEADING','Some database-upgrade SQL statements not installed.');
    define('POPUP_ERROR_TEXT','During the database-upgrade process, some SQL statements could not be executed because they would have created duplicate entries in the database, or the prerequisites (such as column must exist to change or drop) were not met.<br /><br />THE MOST COMMON CAUSE of these failures/exceptions is that you have installed a contribution/add-on that has made alterations to the core database structure. The upgrader is trying to be friendly and not create a problem for you. <br /><br />YOUR STORE MAY WORK JUST FINE without investigating these errors, however, we recommend that you check them out to be sure. <br /><br />If you wish to investigate, you may look at your "upgrade_exceptions" table in the database for details on which statements failed to execute and why.');
  break;
  case ('86'):
    define('POPUP_ERROR_HEADING','PHP Session.use_trans_sid should be disabled.');
    define('POPUP_ERROR_TEXT','The session.use_trans_sid setting in your server\'s PHP.INI file is set to ON. <br /><br />That is BAD, and your hosting company should turn it off. <br><br>This could potentially cause you some problems with session handling and possibly even security concerns.<br /><br />If your hosting company staff are not capable of turning this off to protect everyone on the server, you can try to work around this by setting an .htaccess parameter such as this:<code>php_value session.use_trans_sid off</code>, or you could disable it in your PHP.INI if you have access to it.<br /><br />For more information on the security risks it imposes, see: <a href="http://shh.thathost.com/secadv/2003-05-11-php.txt">http://shh.thathost.com/secadv/2003-05-11-php.txt</a>.<br /><br />(You may have to create the .htaccess file if you don\'t already have one.)');
  break;
  case ('87'):
    define('POPUP_ERROR_HEADING','Permissions Required for Database User');
    define('POPUP_ERROR_TEXT','Zen Cart&reg; operations require the following database-level privileges:<ul><li>ALL PRIVILEGES<br /><em>or</em></li><li>SELECT</li><li>INSERT</li><li>UPDATE</li><li>DELETE</li><li>CREATE</li><li>ALTER</li><li>INDEX</li><li>DROP</li></ul>Day-to-day activities do not normally require the "CREATE" and "DROP" privileges, but these ARE required for Installation, Upgrade, and SQLPatch activities.');
  break;
  case ('88'):
    define('POPUP_ERROR_HEADING','Error encountered while writing /includes/configure.php');
    define('POPUP_ERROR_TEXT','While attempting to save your settings, Zen Cart&reg; Installer was unable to verify successful writing of your configure.php file settings. Please check to be sure that your webserver has full write permissions to the configure.php files shown below.<br /><br />- /includes/configure.php<br />- /admin/includes/configure.php<br /><br />You may want to also check that there is sufficient disk space (or disk quota available to you) in order to write updates to these files. <br /><br />If the files are 0-bytes in size when you encounter this error, then disk space or "available" disk space is likely the cause.<br /><br />Ideal permissions in Unix/Linux hosting is CHMOD 777 until installation is complete. Then they can be set back to 644 or 444 for security after installation is done.<br /><br />If you are running on a Windows host, you may also find it necessary to right-click on each of these files, choose "Properties", then the "Security" tab. Then click on "Add" and select "Everyone", and grant "Everyone" full read/write access until installation is complete. Then reset to read-only after installation.');
  break;
  case ('89'):
    define('POPUP_ERROR_HEADING','GD Support Details');
    define('POPUP_ERROR_TEXT','Zen Cart&reg; uses GD support in PHP, if available, to do image management activities.  It is preferred to have at least version 2.0 available.<br /><br />If GD support is not compiled into your PHP install, you may want to ask your hosting company to do this for you.');
  break;
  case ('90'):
    define('POPUP_ERROR_HEADING','MySQL 5.7 (and higher) not fully supported in v1.5.x');
    define('POPUP_ERROR_TEXT','NOTE: Zen Cart&reg; v2.0 and newer support MySQL 5 and PHP 5 properly.<br />But, you are presently installing v1.6.x.<br /><br />While many efforts have been spent on ensuring that database queries in Zen Cart&reg; v1.5.x are compatible with MySQL 5 releases, the newer v2.x versions are more thoroughly tested.<br /><br />You are welcome to proceed with installation; however, please note that for full compatibility you should use the newer version of Zen Cart.');
  break;
  case ('91'):
    define('POPUP_ERROR_HEADING','PHP Version Alert');
    define('POPUP_ERROR_TEXT','Zen Cart&reg; v1.x is designed to run on PHP versions 5.3.14 and greater.<br /><br />There are several PHP functions used in Zen Cart&reg; which are not available in older PHP versions.<br /><br />You will need to upgrade your PHP version if you intend to use Zen Cart&reg; on this server.');
  break;
  case ('92'):
    define('POPUP_ERROR_HEADING','open_basedir restriction may cause problems');
    define('POPUP_ERROR_TEXT','Your PHP is configured in such a way that prevents you from running scripts outside a specified "basedir" folder. Yet, your website files appear to be kept in a folder outside of the allowed "basedir" area.<br /><br />Among other things, you could have problems uploading files or doing backups.<br /><br />You should talk to your web host to change or remove this restriction.');
  break;
  case ('93'):
    define('POPUP_ERROR_HEADING','cURL support not detected');
    define('POPUP_ERROR_TEXT','Some payment and shipping modules require cURL in order to talk to an external server to request real-time quotes or payment authorizations. <br /><br />If you intend to use the PayPal Express Checkout or PayPal Payments Pro modules, or Authorize.net AIM, you *need* CURL support.<br /><br />It appears that your server may not have cURL support configured or activated for your account. If you need a 3rd-party tool that uses cURL, you will need to talk to your web host to have them install cURL support on your server.<br /><br />More information on CURL can be found at the <a href="http://curl.haxx.se" target="_blank">CURL website</a>');
  break;
  case ('94'):
    define('POPUP_ERROR_HEADING', 'NOTE: PHP "magic_quotes_sybase" is active');
    define('POPUP_ERROR_TEXT', 'It is best to have "magic_quotes_sybase" disabled. When enabled, it can cause unexpected 1064 SQL errors, and other code-execution problems.<br /><br />If you cannot disable it for the whole server, it may be possible to disable via .htaccess or your own php.ini file in your private webspace.  Talk to your hosting company for assistance.');
  break;
  case ('95'):
    define('POPUP_ERROR_HEADING','CURL requires SSL support. Please notify webmaster or hosting company.');
    define('POPUP_ERROR_TEXT','Zen Cart&reg; uses CURL and SSL to communicate with some payment and shipping service providers.<br />The installer has just tested your CURL SSL support and found that it failed.<br /><br />You will not be able to use PayPal or Authorize.net or FirstData/Linkpoint payment modules, and possibly other third-party contributed payment/shipping modules until you enable SSL support in CURL and PHP.<br /><br />More information on CURL can be found at the <a href="http://curl.haxx.se" target="_blank">CURL website</a>');
  break;

}

define('TEXT_VERSION_CHECK_NEW_VER', 'New Version Available v');
define('TEXT_VERSION_CHECK_NEW_PATCH', 'New PATCH Available: v');
define('TEXT_VERSION_CHECK_PATCH', 'patch');
define('TEXT_VERSION_CHECK_DOWNLOAD', 'Download Here');
define('TEXT_VERSION_CHECK_CURRENT', 'Your version of Zen Cart&reg; appears to be current.');
define('TEXT_ERROR_NEW_VERSION_AVAILABLE', '<a href="http://www.zen-cart.com/getit">There is a NEWER version of Zen Cart&reg; available, which you can download from </a><a href="http://www.zen-cart.com" style="text-decoration:underline" target="_blank">www.zen-cart.com</a>');
define('LABEL_ZC_VERSION_CHECK', 'Zen Cart Version:');
