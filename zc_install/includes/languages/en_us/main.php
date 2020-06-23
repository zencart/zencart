<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 19 Modified in v1.5.7 $
 */


return [
'META_TAG_TITLE' => 'Zen Cart&reg; Installer',
'HTML_PARAMS' => 'dir="ltr" lang="en"',
'ZC_VERSION_STRING' => '%s v%s',
'TEXT_PAGE_HEADING_INDEX' => 'System Inspection',
'TEXT_INDEX_FATAL_ERRORS' => 'Some problems that need fixing before we continue',
'TEXT_INDEX_WARN_ERRORS' => 'Some Other Issues:',
'TEXT_INDEX_WARN_ERRORS_ALT' => 'Some Issues:',
'TEXT_HEADER_MAIN' => 'TIP: The field titles are clickable help links which explain what each field means.',
'TEXT_INDEX_HEADER_MAIN' => 'TIP: More information may be available by clicking on the <strong>titles</strong> of some errors and warnings.',
'TEXT_INSTALLER_CHOOSE_LANGUAGE' => 'Installer Language',
'TEXT_HELP_CONTENT_CHOOSE_LANG' => 'This language selection refers to the installation process only.<br>The Zen Cart&reg; installation files include only the US English language pack.<br>However, Zen Cart&reg; is fully multi-lingual, supporting <em>any</em> language (subject to a language pack being available/having been developed by the community).',
'TEXT_PAGE_HEADING_SYSTEM_SETUP' => 'System Setup',
'TEXT_SYSTEM_SETUP_ADMIN_SETTINGS' => 'Admin Settings',
'TEXT_SYSTEM_SETUP_CATALOG_SETTINGS' => 'Catalog (Storefront) Settings',
'TEXT_SYSTEM_SETUP_ADMIN_SERVER_DOMAIN' => 'Admin Server Domain',
'TEXT_SYSTEM_SETUP_ADMIN_SERVER_URL' => 'Admin Server URL',
'TEXT_SYSTEM_SETUP_ADMIN_PHYSICAL_PATH' => 'Admin Physical Path',
'TEXT_SYSTEM_SETUP_CATALOG_ENABLE_SSL' => 'Enable SSL for Storefront?',
'TEXT_SYSTEM_SETUP_CATALOG_HTTP_SERVER_DOMAIN' => 'Storefront HTTP Domain',
'TEXT_SYSTEM_SETUP_CATALOG_HTTP_URL' => 'Storefront HTTP URL',
'TEXT_SYSTEM_SETUP_CATALOG_HTTPS_SERVER_DOMAIN' => 'Storefront HTTPS Domain',
'TEXT_SYSTEM_SETUP_CATALOG_HTTPS_URL' => 'Storefront HTTPS URL',
'TEXT_SYSTEM_SETUP_CATALOG_PHYSICAL_PATH' => 'Storefront Physical Path',
'TEXT_SYSTEM_SETUP_AGREE_LICENSE' => 'Agree to license terms: ',
'TEXT_SYSTEM_SETUP_CLICK_TO_AGREE_LICENSE' => '(Check the box to agree to GPL 2 license terms. Click the title in the left column to view the license.)',
'TEXT_SYSTEM_SETUP_ERROR_DIALOG_TITLE' => 'There are some problems',
'TEXT_SYSTEM_SETUP_ERROR_DIALOG_CONTINUE' => 'Continue anyway',
'TEXT_SYSTEM_SETUP_ERROR_CATALOG_PHYSICAL_PATH' => 'There appears to be a problem with the ' . '%%TEXT_SYSTEM_SETUP_CATALOG_PHYSICAL_PATH%%',
'TEXT_PAGE_HEADING_DATABASE' => 'Database Setup',
'TEXT_DATABASE_HEADER_MAIN' => 'NOTE: You must create your MySQL database and corresponding Database user, and grant permissions to that user, before proceeding on this page. Click the left-column titles below for help with understanding each component.',
'TEXT_DATABASE_SETUP_SETTINGS' => 'Basic Settings',
'TEXT_DATABASE_SETUP_DB_HOST' => 'Database Host: ',
'TEXT_DATABASE_SETUP_DB_USER' => 'Database User: ',
'TEXT_DATABASE_SETUP_DB_PASSWORD' => 'Database Password: ',
'TEXT_DATABASE_SETUP_DB_NAME' => 'Database Name: ',
'TEXT_DATABASE_SETUP_DEMO_SETTINGS' => 'Demo Data',
'TEXT_DATABASE_SETUP_LOAD_DEMO' => 'Load Demo Data',
'TEXT_DATABASE_SETUP_LOAD_DEMO_DESCRIPTION' => 'Load demo data into this database?',
'TEXT_DATABASE_SETUP_ADVANCED_SETTINGS' => 'Advanced Settings',
'TEXT_DATABASE_SETUP_DB_CHARSET' => 'Database Character Set: ',
'TEXT_DATABASE_SETUP_DB_PREFIX' => 'Store Prefix: ',
'TEXT_DATABASE_SETUP_SQL_CACHE_METHOD' => 'SQL Cache Method: ',
'TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS1' => 'Some errors occurred when running the SQL install file',
'TEXT_DATABASE_SETUP_JSCRIPT_SQL_ERRORS2' => '<br>Please see error logs for more details',
'TEXT_DATABASE_SETUP_CHARSET_OPTION_UTF8MB4' => 'utf8mb4 (default setting)',
'TEXT_DATABASE_SETUP_CHARSET_OPTION_UTF8' => 'utf8 (older format)',
'TEXT_DATABASE_SETUP_CHARSET_OPTION_LATIN1' => 'latin1 (much older format)',
'TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_NONE' => 'No SQL Caching',
'TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_DATABASE' => 'Database',
'TEXT_DATABASE_SETUP_CACHE_TYPE_OPTION_FILE' => 'File',
'TEXT_EXAMPLE_DB_HOST' => 'usually "localhost"',
'TEXT_EXAMPLE_DB_USER' => 'enter your MySQL username',
'TEXT_EXAMPLE_DB_PWD' => 'enter the password for your MySQL user',
'TEXT_EXAMPLE_DB_PREFIX' => 'usually best left blank, or use zen_',
'TEXT_EXAMPLE_DB_NAME' => 'enter your MySQL database name',
'TEXT_EXAMPLE_CACHEDIR' => 'usually points to the equivalent of /your/user/home/public_html/zencart/cache folder',
'TEXT_DATABASE_SETUP_CONNECTION_ERROR_DIALOG_TITLE' => 'There are some problems',
'TEXT_CREATING_DATABASE' => 'Creating Database',
'TEXT_LOADING_CHARSET_SPECIFIC' => 'Loading Character Set specific data',
'TEXT_LOADING_DEMO_DATA' => 'Loading Demo Data',
'TEXT_LOADING_PLUGIN_DATA' => 'Loading SQL for Pre-installed Plugins',
'TEXT_COULD_NOT_UPDATE_BECAUSE_ANOTHER_VERSION_REQUIRED' => 'Could not update to version %s. We detect that you currently have v%s, and must perform the updates to get to version %s first.',
'TEXT_PAGE_HEADING_ADMIN_SETUP' => 'Admin Setup',
'TEXT_ADMIN_SETUP_USER_SETTINGS' => 'Admin User Settings',
'TEXT_ADMIN_SETUP_USER_NAME' => 'Admin Superuser Name: ',
'TEXT_EXAMPLE_USERNAME' => 'ie: bill',
'TEXT_ADMIN_SETUP_USER_EMAIL' => 'Admin Superuser Email: ',
'TEXT_EXAMPLE_EMAIL' => 'ie: my_email@example.com',
'TEXT_ADMIN_SETUP_USER_EMAIL_REPEAT' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Retype email: ',
'TEXT_ADMIN_SETUP_USER_PASSWORD' => 'Admin password: ',
'TEXT_ADMIN_SETUP_USER_PASSWORD_HELP' => '<strong>REMEMBER THIS!!</strong>: Below is your initial temporary password for your Admin Superuser Account. Please ensure you make a note of it.',
'TEXT_ADMIN_SETUP_ADMIN_DIRECTORY' => 'Admin Directory: ',
'TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_DEFAULT' => 'We were not able to change the name of your admin directory automatically. You must rename it from "admin" before you can access the admin area.',
'TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_NOT_ADMIN_CHANGED' => 'We did not change your admin directory automatically as it already seems to have been changed from the default.',
'TEXT_ADMIN_SETUP_ADMIN_DIRECTORY_HELP_CHANGED' => 'Your store admin directory may have been automatically renamed. The new/current name is shown below. You may change this name to anything other than the default ("admin").',
'TEXT_ADMIN_SETUP_NEWSLETTER_SETTINGS' => 'Newsletter',
'TEXT_ADMIN_SETUP_NEWSLETTER_EMAIL' => 'Newsletter Email: ',
'TEXT_ADMIN_SETUP_NEWSLETTER_OPTIN' => 'Opt In: ',
'TEXT_PAGE_HEADING_COMPLETION' => 'Setup Complete',
'TEXT_COMPLETION_HEADER_MAIN' => '',
'TEXT_COMPLETION_INSTALL_COMPLETE' => '',
'TEXT_COMPLETION_INSTALL_LINKS_BELOW' => 'The links to access your Admin area dashboard and shop storefront are shown below:',
'TEXT_COMPLETION_UPGRADE_COMPLETE' => 'Congratulations, your upgrade is now complete.',
'TEXT_COMPLETION_ADMIN_DIRECTORY_WARNING' => 'Your admin directory could not be renamed automatically, you will need to rename your admin directory before accessing it',
'TEXT_COMPLETION_INSTALLATION_DIRECTORY_WARNING' => 'You must <strong>now</strong> delete/remove the "zc_install" directory from your server.',
'TEXT_COMPLETION_INSTALLATION_DIRECTORY_EXPLANATION' => 'Admin access is disabled until this directory has been removed, to prevent unauthorized persons from using the installer to overwrite your installation.',
'TEXT_COMPLETION_CATALOG_LINK_TEXT' => 'Your Storefront',
'TEXT_COMPLETION_ADMIN_LINK_TEXT' => 'Your Admin Dashboard',
'TEXT_PAGE_HEADING_DATABASE_UPGRADE' => 'Database Upgrade',
'TEXT_DATABASE_UPGRADE_HEADER_MAIN' => '',
'TEXT_DATABASE_UPGRADE_STEPS_DETECTED' => 'Your database has been examined and the following list shows the upgrade steps required for your database.<br>Please ensure you have a <em>verified</em> backup of your database prior to proceeding with this upgrade/modification process.',
'TEXT_DATABASE_UPGRADE_LEGEND_UPGRADE_STEPS' => 'Please confirm your desired upgrade steps',
'TEXT_DATABASE_UPGRADE_ADMIN_CREDENTIALS' => 'Admin Credentials (SuperUser)',
'TEXT_VALIDATION_ADMIN_CREDENTIALS' => 'To authorize the database upgrade, you must enter an admin username and password with SuperUser permissions in your store.',
'TEXT_HELP_TITLE_UPGRADEADMINNAME' => '%%TEXT_DATABASE_UPGRADE_ADMIN_CREDENTIALS%%',
'TEXT_HELP_CONTENT_UPGRADEADMINNAME' => 'To authorize the database upgrade, you must enter an admin username and password with SuperUser (ie: unrestricted) permissions in your store.<br>This will be the username and password you use to log in to the Admin area of your store.<br>(It is NOT your FTP password, and is not your hosting control panel password. Nobody knows this password except you or your storeowner. You can not get it from your hosting company.)<br>If you are locked out of your store and do not know any valid admin passwords and cannot log in to your store Admin page, then you can do an aggressive reset of the password by following the instructions in this article: <a href="https://docs.zen-cart.com/user/troubleshooting/reset_admin_password/" rel="noopener" target="_blank">https://docs.zen-cart.com/user/troubleshooting/reset_admin_password/</a>.',
'TEXT_DATABASE_UPGRADE_ADMIN_USER' => 'User Name',
'TEXT_DATABASE_UPGRADE_ADMIN_PASSWORD' => 'Password',
'TEXT_HELP_TITLE_UPGRADEADMINPWD' => 'Admin Password for Upgrade',
'TEXT_HELP_CONTENT_UPGRADEADMINPWD' => '%%TEXT_HELP_CONTENT_UPGRADEADMINNAME%%',
'TEXT_VALIDATION_ADMIN_PASSWORD' => 'A valid password is required',
'TEXT_ERROR_ADMIN_CREDENTIALS' => 'Could not verify the Admin Credentials you provided.<br><br>' . '%%TEXT_HELP_CONTENT_UPGRADEADMINNAME%%',
'TEXT_UPGRADE_IN_PROGRESS' => 'Upgrade running. Progress of each step is indicated below ...',
'TEXT_UPGRADE_TO_VER_X_COMPLETED' => 'Upgrade to version %s completed.',
'TEXT_NO_REMAINING_UPGRADE_STEPS' => 'Looking good! It appears as though there are no more upgrade steps required.',
'TEXT_CONTINUE' => 'Continue',
'TEXT_CANCEL' => 'Cancel',
'TEXT_CONTINUE_FIX' => 'Return and Fix',
'TEXT_REFRESH' => 'Refresh',
'TEXT_UPGRADE' => 'Upgrade ...',
'TEXT_CLEAN_INSTALL' => 'Clean Install',
'TEXT_UPDATE_CONFIGURE' => 'Update Configure File',
'TEXT_NAVBAR_SYSTEM_INSPECTION' => 'System Inspection',
'TEXT_NAVBAR_SYSTEM_SETUP' => 'System Setup',
'TEXT_NAVBAR_DATABASE_UPGRADE' => 'Database Upgrade',
'TEXT_NAVBAR_DATABASE_SETUP' => 'Database Setup',
'TEXT_NAVBAR_ADMIN_SETUP' => 'Admin Setup',
'TEXT_NAVBAR_COMPLETION' => 'Finished',
'TEXT_NAVBAR_PAYMENT_PROVIDERS' => 'Payment Providers',
'TEXT_INDEX_ALERTS' => 'Alerts',
'TEXT_ERROR_PROBLEMS_WRITING_CONFIGUREPHP_FILES' => 'There were problems preparing and storing the configure.php files. YOUR INSTALL DID NOT COMPLETE!<br>Additional technical details may be found in your /logs/ folder.',
'TEXT_ERROR_COULD_NOT_READ_CFGFILE_TEMPLATE' => 'Could not read the master config file layout: %s. Please ensure the file exists and is readable.',
'TEXT_ERROR_COULD_NOT_WRITE_CONFIGFILE' => 'Could not write the generated config file: %s. Please ensure the file exists and is writable.',
'TEXT_ERROR_STORE_CONFIGURE' => 'Catalog /includes/configure.php file either does not exist, is not readable or is not writeable',
'TEXT_ERROR_ADMIN_CONFIGURE' => 'Admin /admin/includes/configure.php file either does not exist, is not readable or is not writeable',
'TEXT_ERROR_PHP_VERSION' => str_replace(["\n", "\r"], '', 'Incorrect PHP Version.<p>The PHP version you are using (' . PHP_VERSION . ') is not suitable.</p><p>This version of Zen Cart&reg; is compatible with PHP versions 5.6 to 7.4.x, although 7.2.x or higher is recommended.<br>Check the <a href="https://www.zen-cart.com">www.zen-cart.com</a> website for the latest version of Zen Cart&reg;.</p><p>For more information about version compatibility and server requirements see <a href="https://docs.zen-cart.com/user/first_steps/server_requirements/">Zen Cart Server Requirements</a></p>'),
'TEXT_ERROR_PHP_VERSION_RECOMMENDED' => '<p>For maximum security and compatibility you should be using PHP %s or newer. This installation can proceed, but note that your site will not be PCI Compliant when running out-of-date software.</p><p>Consult the <a href="https://php.net">PHP Website</a> for specifics.</p><p>For more information about version compatibility and server requirements see <a href="https://docs.zen-cart.com/user/first_steps/server_requirements/">Zen Cart Server Requirements</a></p>',
'TEXT_ERROR_PHP_VERSION_MIN' => 'PHP Version should be greater than %s',
'TEXT_ERROR_PHP_VERSION_MAX' => 'PHP Version should be less than %s',
'TEXT_ERROR_MYSQL_SUPPORT' => 'Problems with your MySQL (mysqli) support. Your server appears to be missing the mysqli extension for PHP, and without it we cannot connect to your database. Talk to your hosting company for assistance.',
'TEXT_ERROR_LOG_FOLDER' => DIR_FS_LOGS . ' folder is not writeable',
'TEXT_ERROR_CACHE_FOLDER' => DIR_FS_SQL_CACHE . ' folder is not writeable',
'TEXT_ERROR_IMAGES_FOLDER' => '/images/ folder is not writeable',
'TEXT_ERROR_DEFINEPAGES_FOLDER' => '/includes/languages/english/html_includes/ folder is not writeable',
'TEXT_ERROR_MEDIA_FOLDER' => '/media/ folder is not writeable',
'TEXT_ERROR_PUB_FOLDER' => DIR_FS_DOWNLOAD_PUBLIC . ' folder is not writeable',
'TEXT_ERROR_CONFIGURE_REQUIRES_UPDATE' => 'Your configure.php file is an old version and requires updating before we can continue.',
'TEXT_ERROR_HTACCESS_SUPPORT' => 'Support for ".htaccess" files is not enabled or is not operating correctly.<br>[ <i><strong>NOTE:</strong> If you are using Nginx, continue to the <u>END</u> of this Installation Wizard for information on resolving this issue.<i> ]',
'TEXT_ERROR_SESSION_SUPPORT' => 'Problems with session support',
'TEXT_ERROR_SESSION_SUPPORT_USE_TRANS_SID' => 'ini setting session.use_trans_sid is enabled',
'TEXT_ERROR_SESSION_SUPPORT_AUTO_START' => 'ini setting session.auto_start is enabled',
'TEXT_ERROR_DB_CONNECTION' => 'Problems with Database Connection',
'TEXT_ERROR_DB_CONNECTION_DEFAULT' => 'Possible problems with database connection',
'TEXT_ERROR_DB_CONNECTION_UPGRADE' => 'Problems with database connection based on the entries in your current configure.php',
'TEXT_ERROR_SET_TIME_LIMIT' => 'max_execution_time setting disabled',
'TEXT_ERROR_GD' => 'GD Extension not enabled',
'TEXT_ERROR_ZLIB' => 'Zlib Extension not enabled',
'TEXT_ERROR_OPENSSL' => 'Openssl Extension not enabled',
'TEXT_ERROR_CURL' => 'Problems with the CURL extension - PHP is reporting that CURL is not present.',
'TEXT_ERROR_UPLOADS' => 'Upload Extension for PHP is not enabled',
'TEXT_ERROR_XML' => 'XML Extension in PHP is not enabled',
'TEXT_ERROR_GZIP' => 'The GZip Extension in PHP is not enabled<br>[ <i><strong>NOTE:</strong> This may not be relevant if you are using Nginx and handling GZip within Nginx.</i> ]',
'TEXT_ERROR_EXTENSION_NOT_LOADED' => '%s extension does not seem to be loaded',
'TEXT_ERROR_FUNCTION_DOES_NOT_EXIST' => 'PHP function %s does not exist',
'TEXT_ERROR_CURL_LIVE_TEST' => 'Could not use CURL to contact a live server',
'TEXT_ERROR_HTTPS' => 'PRO TIP: If possible, you should already have installed an SSL certificate and should run the installer using "https://"',
'TEXT_ERROR_SUCCESS_EXISTING_CONFIGURE' => 'An existing configure.php file was found, indicating a pre-existing installation.',
'TEXT_ERROR_SUCCESS_EXISTING_CONFIGURE_NO_UPDATE' => 'An existing configure.php file was found. However, your database seems to be current. This suggests you are on a live site. Proceeding with Install will delete the current database contents! Are you sure you want to continue with Install?',
'TEXT_ERROR_MULTIPLE_ADMINS_NONE_SELECTED' => 'Multiple Admin directories seem to exist. Either remove the duplicate admin directories and click Refresh or select the correct admin directory below and click Refresh.',
'TEXT_ERROR_MULTIPLE_ADMINS_SELECTED' => 'Multiple Admin directories seem to exist. If the selected directory below is incorrect, please choose another and click Refresh.',
'TEXT_ERROR_SUCCESS_NO_ERRORS' => 'No errors were detected on your system. You may continue with the installation.',
'TEXT_UPGRADE_INFO' => '%%TEXT_UPGRADE%%: will inspect your database and subsequently offer the steps required to upgrade to the current version (adding new fields/modifying existing fields). This is intended to be a non-destructive process, but as with all modifications you must ensure you have a verified backup of your database available before proceeding.',
'TEXT_CLEAN_INSTALL_INFO' => '%%TEXT_CLEAN_INSTALL%%: will revert the database to a new state, deleting all data. Optionally, the demonstration data may be loaded as part of this process.',
'TEXT_FORM_VALIDATION_REQUIRED' => 'Required',
'TEXT_FORM_VALIDATION_AGREE_LICENSE' => 'You must agree to the license terms',
'TEXT_FORM_VALIDATION_CATALOG_HTTPS_URL' => 'A URL is required here, even if you have temporarily opted not to enable SSL yet. Try using your normal domain name.',
'TEXT_NAVBAR_INSTALLATION_INSTRUCTIONS' => 'Installation Instructions',
'TEXT_NAVBAR_FORUM_LINK' => 'Forum',
'TEXT_NAVBAR_WIKI_LINK' => 'Wiki',
'TEXT_HELP_TITLE_HTACCESSSUPPORT' => '.htaccess support',
'TEXT_HELP_CONTENT_HTACCESSSUPPORT' => 'There appears to be a problem with support for ".htaccess" files.<br>Sensitive files and folders on your site, that should normally be blocked by security rules in the built-in ".htaccess" files that come with Zen Cart, are currently accessible.<br><br>Possible causes:
<ul style="list-style-type:square"><li>You may not be using Apache as your Web Server (".htaccess" files are unique to the Apache Web Server), or,</li><li>Support for ".htaccess" is disabled or misconfigured, or,</li><li>The ".htaccess" files that come with Zen Cart have not been uploaded to your site.<br><strong><i>Files starting with ".", such as ".htaccess" files, are usually treated as "hidden" files and your FTP program may have failed to upload these if you have turned off the display and/or transfer of such hidden files in its settings.</i></strong></li></ul><br>
You may proceed with installing despite this situation, but please be advised that your site will be less secure than it ought to be (If using the Apache Web Server).<br><br>If you are using the Nginx Web Server, please proceed with installing and secure your installation using the equivalent Nginx directives provided under "<strong>Important Security Information for Nginx</strong>" in the "Setup Finished" section of this installation wizard.<br><br>If you do not know which Web Server is in use, please proceed on the assumption that it is the Apache Web Server and request assistance with enabling ".htaccess" support from your web hosting provider.<br><br>',
'TEXT_HELP_TITLE_FOLDERPERMS' => 'Folder Permissions',
'TEXT_HELP_CONTENT_FOLDERPERMS' => 'The permissions for this folder are not set correctly. This folder needs to be writeable. You can find out more about folder permissions at <a href="https://docs.zen-cart.com/user/installing/permissions/" rel="noopener" target="_blank">https://docs.zen-cart.com/user/installing/permissions/</a>',
'TEXT_HELP_TITLE_CONNECTIONDATABASECHECK' => 'Initial Database Connection',
'TEXT_HELP_CONTENT_CONNECTIONDATABASECHECK' => 'We tried to connect to MySQL using a localhost connection. This failure does not necessarily mean MySQL is not working, as some hosts require an IP address or host name for the MySQL database.<br><br>If you are indeed using localhost for your database server, you should check that MySQL is running correctly.',
'TEXT_HELP_TITLE_CHECKCURL' => '%%TEXT_ERROR_CURL%%',
'TEXT_HELP_CONTENT_CHECKCURL' => 'CURL is a background process used by (PHP in) your store to connect to external servers and services such as payment and shipping providers to process transactions or get real-time shipping quotes. When we tested CURL functionality on your server we were unable to establish a connection. This could indicate a problem with your webserver configuration. Please contact your hosting company for assistance to enable CURL support on your server.<br><br>If you are a developer running this site on an offline development server then it is unsurprising that CURL cannot connect for this test. CURL is not necessary for development purposes except for testing transactional activity, at which time connecting online will be required.',
'TEXT_HELP_TITLE_ADMINSERVERDOMAIN' => 'Admin Server Domain',
'TEXT_HELP_CONTENT_ADMINSERVERDOMAIN' => 'Enter the domain name for accessing your Admin area. It is strongly recommended to use HTTPS (SSL) for this address. Consult your hosting company about enabling SSL on your site.',
'TEXT_HELP_TITLE_ENABLESSLCATALOG' => 'Enable SSL for Storefront?',
'TEXT_HELP_CONTENT_ENABLESSLCATALOG' => 'Check this box if you have an SSL certificate on your hosting account and you want Zen Cart&reg; to use it when displaying sensitive pages such as Login, My Account, Checkout, etc.',
'TEXT_HELP_TITLE_HTTPSERVERCATALOG' => 'Storefront HTTP Domain',
'TEXT_HELP_CONTENT_HTTPSERVERCATALOG' => 'Enter the domain-part of the URL for your store. eg: http://www.example.com',
'TEXT_HELP_TITLE_HTTPURLCATALOG' => 'Storefront HTTP URL',
'TEXT_HELP_CONTENT_HTTPURLCATALOG' => 'Enter the entire URL for your store.  eg: http://www.example.com/zencart/',
'TEXT_HELP_TITLE_HTTPSSERVERCATALOG' => 'Storefront HTTPS Domain',
'TEXT_HELP_CONTENT_HTTPSSERVERCATALOG' => 'If you have checked the box above to enable use of SSL during checkout, you must enter here the domain-part of the https URL to your store.<br>This is typically something like:<br>https://www.example.com<br>https://www.hostingcompany.com/~username<br>https://www.hostingcompany.com/~username/subdomain.com',
'TEXT_HELP_TITLE_HTTPSURLCATALOG' => 'Storefront HTTPS URL',
'TEXT_HELP_CONTENT_HTTPSURLCATALOG' => 'Enter the https URL to your store. This is typically the same as the HTTPS Domain, followed by the foldername in which your store\'s files are kept. eg: https://www.example.com/zencart',
'TEXT_HELP_TITLE_PHYSICALPATH' => 'Storefront Physical Path',
'TEXT_HELP_CONTENT_PHYSICALPATH' => 'This is the actual path (according to your server\'s filesystem) where your Zen Cart&reg; files are located. Common examples look like "/users/home/public_html/zencart". <br>It is important to provide the correct complete path, else Zen Cart&reg; will not be able to find its files in order to run.',
'TEXT_HELP_TITLE_DBHOST' => 'Database Host',
'TEXT_HELP_CONTENT_DBHOST' => 'What is the database host?<br>The database host can be in the form of a host name, such as "localhost" or "db1.myserver.com", or as an IP Address, such as "192.168.0.1". Most hosting companies use "localhost" here. <br>Your hosting company can tell you what to use, and this information is usually shown on the screen in their control panel where you create the database and assign user permissions to the database.<br>If you need assistance finding this information, consult your hosting company online FAQ documentation.',
'TEXT_HELP_TITLE_DBUSER' => 'Database User',
'TEXT_HELP_CONTENT_DBUSER' => 'What is the MySQL username used to connect to the database?<br>An example username is "myusername_store".<br>For PCI reasons you should NEVER use "root" here when running on a server connected to the internet.<br><br>This MySQL user needs the following permissions granted to it: ALTER, CREATE, DELETE, DROP, INDEX, INSERT, LOCK TABLES, SELECT, UPDATE (or just "Grant All").',
'TEXT_HELP_TITLE_DBPASSWORD' => 'Database Password',
'TEXT_HELP_CONTENT_DBPASSWORD' => 'What is the password assigned to the MySQL username you created for this database?',
'TEXT_HELP_TITLE_DBNAME' => 'Database Name',
'TEXT_HELP_CONTENT_DBNAME' => 'What is the name of the database used to hold the data?<br>An example database name is "zencart" or "myaccount_zencart".<br>NOTE: <strong>You</strong> must create this database BEFORE you can proceed with the Zen Cart&reg; installation.<br>You can create your MySQL database using your hosting company\'s control panel.',
'TEXT_HELP_TITLE_DEMODATA' => '%%TEXT_DATABASE_SETUP_LOAD_DEMO%%',
'TEXT_HELP_CONTENT_DEMODATA' => 'Demo Data sets up some example categories and products, with a range of product attributes (variants), discounts, sales, special offers etc. These are useful for new users to investigate the capabilities of the shop and how these product options are displayed.<br><br>Subsequently you may delete the demo products manually, or you can re-run this Clean Install process without the Demo Data to start afresh with your own products.',
'TEXT_HELP_TITLE_DBCHARSET' => 'Database Character Set',
'TEXT_HELP_CONTENT_DBCHARSET' => 'Most stores will use utf8mb4 or utf8.<br>If you don\'t have a reason to use something else, use utf8mb4.',
'TEXT_HELP_TITLE_DBPREFIX' => 'Database Tablename Prefix',
'TEXT_HELP_CONTENT_DBPREFIX' => 'What prefix would you like to use for the Zen Cart&reg; database tables?<br>Historically it was common on shared hosting to have a very limited number of databases offered. Consequently several applications may have shared a single database, using table prefixes to group and separate each applications tables.<br>Example: <strong>zen_</strong><br><strong class="alert">TIP: Leave empty if no prefix is needed.</strong>',
'TEXT_HELP_TITLE_SQLCACHEMETHOD' => 'SQL Cache Method',
'TEXT_HELP_CONTENT_SQLCACHEMETHOD' => 'Default setting is "none". Alternatives are "database" or "file". If your server is really slow, use "none". If your site is moderately busy, use "database". If your site is extremely high traffic, use "file". ',
'TEXT_HELP_TITLE_SQLCACHEDIRECTORY' => 'SQL Cache Directory',
'TEXT_HELP_CONTENT_SQLCACHEDIRECTORY' => 'Enter the directory to use for file-based caching. This is a directory/folder on your webserver, and its permissions must be set to writable so that the webserver (eg Apache) can write files to it.',
'TEXT_HELP_TITLE_ADMINUSER' => 'Admin Superuser Name',
'TEXT_HELP_CONTENT_ADMINUSER' => 'This will be the primary username used to manage your admin access and other admin user accounts. It will have unrestricted privileges.',
'TEXT_HELP_TITLE_ADMINEMAIL' => 'Admin Superuser Email',
'TEXT_HELP_CONTENT_ADMINEMAIL' => 'This email address will be used for password recovery in case you forget your password.',
'TEXT_HELP_TITLE_ADMINEMAIL2' => 'Retype Email',
'TEXT_HELP_CONTENT_ADMINEMAIL2' => 'Please re-enter the email address. This is just to help catch accidental typos!',
'TEXT_HELP_TITLE_ADMINPASSWORD' => 'Admin Superuser Password',
'TEXT_HELP_CONTENT_ADMINPASSWORD' => '<strong>NOTE THIS PASSWORD NOW!!!!!</strong> - you will need it for the initial access to the Admin area.<br>This is the password assigned to the admin username you specified above. You may be asked to change it on first login or you may change it once logged into the Admin area.',
'TEXT_HELP_TITLE_ADMINDIRECTORY' => 'Admin Directory',
'TEXT_HELP_CONTENT_ADMINDIRECTORY' => 'We try to rename your admin folder for you automatically, to offer a degree of security-by-obscurity. While we understand that this doesn\'t make it foolproof, it does discourage unauthorized visitors from attacking your site. You may still consider changing the foldername yourself (just rename the folder to whatever you wish it to be, by using your FTP program or your hosting company\'s File Manager tool in your hosting control panel).',
'TEXT_VERSION_CHECK_NEW_VER' => 'New Version Available v',
'TEXT_VERSION_CHECK_NEW_PATCH' => 'New PATCH Available: v',
'TEXT_VERSION_CHECK_PATCH' => 'patch',
'TEXT_VERSION_CHECK_DOWNLOAD' => 'Download Here',
'TEXT_VERSION_CHECK_CURRENT' => 'Your version of Zen Cart&reg; appears to be current.',
'TEXT_ERROR_NEW_VERSION_AVAILABLE' => '<a href="https://www.zen-cart.com/files">There is a NEWER version of Zen Cart&reg; available, which you can download from </a><a href="https://www.zen-cart.com/files" style="text-decoration:underline" rel="noopener" target="_blank">www.zen-cart.com/files</a>',
'TEXT_DB_VERSION_NOT_FOUND' => 'A Zen Cart database for %s was not found!',
'REASON_TABLE_ALREADY_EXISTS' => 'Cannot create table %s because it already exists',
'REASON_TABLE_DOESNT_EXIST' => 'Cannot drop table %s because it does not exist.',
'REASON_TABLE_NOT_FOUND' => 'Cannot execute because table %s does not exist.',
'REASON_CONFIG_KEY_ALREADY_EXISTS' => 'Cannot insert configuration_key "%s" because it already exists',
'REASON_COLUMN_ALREADY_EXISTS' => 'Cannot ADD column %s because it already exists.',
'REASON_COLUMN_DOESNT_EXIST_TO_DROP' => 'Cannot DROP column %s because it does not exist.',
'REASON_COLUMN_DOESNT_EXIST_TO_CHANGE' => 'Cannot CHANGE column %s because it does not exist.',
'REASON_PRODUCT_TYPE_LAYOUT_KEY_ALREADY_EXISTS' => 'Cannot insert prod-type-layout configuration_key "%s" because it already exists',
'REASON_INDEX_DOESNT_EXIST_TO_DROP' => 'Cannot drop index %s on table %s because it does not exist.',
'REASON_PRIMARY_KEY_DOESNT_EXIST_TO_DROP' => 'Cannot drop primary key on table %s because it does not exist.',
'REASON_INDEX_ALREADY_EXISTS' => 'Cannot add index %s to table %s because it already exists.',
'REASON_PRIMARY_KEY_ALREADY_EXISTS' => 'Cannot add primary key to table %s because a primary key already exists.',
'REASON_CONFIG_GROUP_KEY_ALREADY_EXISTS' => 'Cannot insert configuration_group_key "%s" because it already exists',
'REASON_CONFIG_GROUP_ID_ALREADY_EXISTS' => 'Cannot insert configuration_group_id "%s" because it already exists',
'TEXT_COMPLETION_NGINX_TEXT' => '<u>Important Security Information for Nginx</u>',
'TEXT_HELP_TITLE_NGINXCONF' => 'Securing Zen Cart on Nginx Web Servers',
'TEXT_HELP_CONTENT_NGINXCONF' => '<p>Your Zen Cart installation comes with security measures in a format native to the Apache Webserver. <br>
See below to implement a similar set of measures for the Nginx Webserver.</p>
<hr>
<ul style="list-style-type:square">
<li>Go to your <strong>"zc_install/includes/nginx_conf"</strong> folder and open the following files using a text editor such as notepad or textedit:
  <ul style="list-style-type:circle">
    <li>zencart_ngx_http.conf</li>
    <li>zencart_ngx_server.conf</li>
  </ul>
</li>
<li>Add the contents of <strong>"zencart_ngx_http.conf"</strong> under the <strong>"http"</strong> section of your Nginx configuration file.
  <ul style="list-style-type:circle">
    <li>Edit the caching durations in the <strong>"map"</strong> block to suit as required</li>
  </ul>
</li>
<li>Add the contents of <strong>"zencart_ngx_server.conf"</strong> to the relevant <strong>"server"</strong> block for Zen Cart in your Nginx configuration file.
  <ul style="list-style-type:circle">
    <li>The directives may be used for SSL and/or Non SSL server blocks.</li>
    <li>The directives should be placed at the beginning of the server block before any other location blocks.
      <ul style="list-style-type:none">
        <li>- The order in which the directives appear is important.</li>
        <li>- Do not change this order without fully understanding the directives and implications.</li>
      </ul>
  </ul>
</li>
<li>It is especially critical that these directives appear before any generic php handling location blocks such as ... <br>
  <pre><code>location ~ \.php { <strong>Nginx PHP Handling Directives;</strong> }</code></pre>
  ... or any other location blocks that might be processed before these are.</li>
<li>Instead, edit the <strong>"zencart_php_handler"</strong> location block to match your Nginx PHP Handling Directives.
  <ul style="list-style-type:circle">
    <li>Simply duplicate the contents of your existing PHP handling location block.
      <ul style="list-style-type:none">
        <li>- That is, copy and paste in the equivalent Nginx PHP Handling Directives.</li>
        <li>- If you do not have an existing PHP handling location block, please refer to available guides such as from <a href="https://www.nginx.com/resources/wiki/start/topics/examples/phpfcgi/" rel="noopener" target="_blank"><u>The Nginx Website</u></a>.</li>
      </ul>
    </li>
  </ul>
</li>
<li>If using plugins for "Pretty URLs", insert the relevant directives into the specified block.</li>
<li>Reload Nginx.
  <ul style="list-style-type:circle">
    <li>Do this before closing this dialog box.</li>
    <li>Remember to delete the <strong>"zc_install"</strong> folder when done.
      <ul style="list-style-type:none">
        <li>- Including the <strong>"zc_install/includes/nginx_conf"</strong> folder and its contents.</li>
      </ul>
    </li>
  </ul>
</li>
<ol>
</div>
<div class="alert-box alert"> <strong>IMPORTANT:</strong> These location blocks should be <strong>BEFORE</strong> any other location blocks in your Nginx configuration server block for Zen Cart.</div>
<hr>',
'TEXT_HELP_TITLE_AGREETOTERMS' => 'Agree To Terms',
'TEXT_HELP_CONTENT_AGREETOTERMS' => '<a href="http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html" rel="noopener" target="_blank">Original GPL 2.0 text</a>
<h2>The GNU General Public License (GPL)</h2>
<h3>Version 2, June 1991</h3>
<tt>
<p> Copyright (C) 1989, 1991 Free Software Foundation, Inc.<br>
  59 Temple Place, Suite 330, Boston, MA 02111-1307 USA</p>
<p> Everyone is permitted to copy and distribute verbatim copies<br>
  of this license document, but changing it is not allowed.</p>
<strong>
<p>Preamble</p>
</strong>
<p>The licenses for most software are designed to take away your freedom to share and change it. By contrast, the GNU General Public License is intended to guarantee your freedom to share and change free software--to make sure the software is free for all its users. This General Public License applies to most of the Free Software Foundation\'s software and to any other program whose authors commit to using it. (Some other Free Software Foundation software is covered by the GNU Library General Public License instead.) You can apply it to your programs, too.</p>
<p>When we speak of free software, we are referring to freedom, not price. Our General Public Licenses are designed to make sure that you have the freedom to distribute copies of free software (and charge for this service if you wish), that you receive source code or can get it if you want it, that you can change the software or use pieces of it in new free programs; and that you know you can do these things.</p>
<p> To protect your rights, we need to make restrictions that forbid anyone to deny you these rights or to ask you to surrender the rights. These restrictions translate to certain responsibilities for you if you distribute copies of the software, or if you modify it.</p>
<p>For example, if you distribute copies of such a program, whether gratis or for a fee, you must give the recipients all the rights that you have. You must make sure that they, too, receive or can get the source code. And you must show them these terms so they know their rights.</p>
<p>We protect your rights with two steps: (1) copyright the software, and (2) offer you this license which gives you legal permission to copy, distribute and/or modify the software.</p>
<p>Also, for each author\'s protection and ours, we want to make certain that everyone understands that there is no warranty for this free software. If the software is modified by someone else and passed on, we want its recipients to know that what they have is not the original, so that any problems introduced by others will not reflect on the original authors\' reputations.</p>
<p>Finally, any free program is threatened constantly by software patents. We wish to avoid the danger that redistributors of a free program will individually obtain patent licenses, in effect making the program proprietary. To prevent this, we have made it clear that any patent must be licensed for everyone\'s free use or not licensed at all.</p>
<p>The precise terms and conditions for copying, distribution and modification follow.</p>
<strong>
<p>TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION</p>
</strong>
<p><strong>0</strong>. This License applies to any program or other work which contains a notice placed by the copyright holder saying it may be distributed under the terms of this General Public License. The "Program", below, refers to any such program or work, and a "work based on the Program" means either the Program or any derivative work under copyright law: that is to say, a work containing the Program or a portion of it, either verbatim or with modifications and/or translated into another language. (Hereinafter, translation is included without limitation in the term "modification".) Each licensee is addressed as "you".</p>
<p>Activities other than copying, distribution and modification are not covered by this License; they are outside its scope. The act of running the Program is not restricted, and the output from the Program is covered only if its contents constitute a work based on the Program (independent of having been made by running the Program). Whether that is true depends on what the Program does.</p>
<p><strong>1</strong>. You may copy and distribute verbatim copies of the Program\'s source code as you receive it, in any medium, provided that you conspicuously and appropriately publish on each copy an appropriate copyright notice and disclaimer of warranty; keep intact all the notices that refer to this License and to the absence of any warranty; and give any other recipients of the Program a copy of this License along with the Program.</p>
<p>You may charge a fee for the physical act of transferring a copy, and you may at your option offer warranty protection in exchange for a fee.</p>
<p> <strong>2</strong>. You may modify your copy or copies of the Program or any portion of it, thus forming a work based on the Program, and copy and distribute such modifications or work under the terms of Section 1 above, provided that you also meet all of these conditions:</p>
<blockquote>
  <p>a) You must cause the modified files to carry prominent notices stating that you changed the files and the date of any change.</p>
  <p>b) You must cause any work that you distribute or publish, that in whole or in part contains or is derived from the Program or any part thereof, to be licensed as a whole at no charge to all third parties under the terms of this License.</p>
  <p>c) If the modified program normally reads commands interactively when run, you must cause it, when started running for such interactive use in the most ordinary way, to print or display an announcement including an appropriate copyright notice and a notice that there is no warranty (or else, saying that you provide a warranty) and that users may redistribute the program under these conditions, and telling the user how to view a copy of this License. (Exception: if the Program itself is interactive but does not normally print such an announcement, your work based on the Program is not required to print an announcement.)</p>
</blockquote>
<p>These requirements apply to the modified work as a whole. If identifiable sections of that work are not derived from the Program, and can be reasonably considered independent and separate works in themselves, then this License, and its terms, do not apply to those sections when you distribute them as separate works. But when you distribute the same sections as part of a whole which is a work based on the Program, the distribution of the whole must be on the terms of this License, whose permissions for other licensees extend to the entire whole, and thus to each and every part regardless of who wrote it.</p>
<p>Thus, it is not the intent of this section to claim rights or contest your rights to work written entirely by you; rather, the intent is to exercise the right to control the distribution of derivative or collective works based on the Program.</p>
<p>In addition, mere aggregation of another work not based on the Program with the Program (or with a work based on the Program) on a volume of a storage or distribution medium does not bring the other work under the scope of this License.</p>
<p><strong>3</strong>. You may copy and distribute the Program (or a work based on it, under Section 2) in object code or executable form under the terms of Sections 1 and 2 above provided that you also do one of the following:</p>
<blockquote>
  <p>a) Accompany it with the complete corresponding machine-readable source code, which must be distributed under the terms of Sections 1 and 2 above on a medium customarily used for software interchange; or,</p>
  <p> b) Accompany it with a written offer, valid for at least three years, to give any third party, for a charge no more than your cost of physically performing source distribution, a complete machine-readable copy of the corresponding source code, to be distributed under the terms of Sections 1 and 2 above on a medium customarily used for software interchange; or,</p>
  <p>c) Accompany it with the information you received as to the offer to distribute corresponding source code. (This alternative is allowed only for noncommercial distribution and only if you received the program in object code or executable form with such an offer, in accord with Subsection b above.)</p>
</blockquote>
<p>The source code for a work means the preferred form of the work for making modifications to it. For an executable work, complete source code means all the source code for all modules it contains, plus any associated interface definition files, plus the scripts used to control compilation and installation of the executable. However, as a special exception, the source code distributed need not include anything that is normally distributed (in either source or binary form) with the major components (compiler, kernel, and so on) of the operating system on which the executable runs, unless that component itself accompanies the executable.</p>
<p>If distribution of executable or object code is made by offering access to copy from a designated place, then offering equivalent access to copy the source code from the same place counts as distribution of the source code, even though third parties are not compelled to copy the source along with the object code.</p>
<p><strong>4</strong>. You may not copy, modify, sublicense, or distribute the Program except as expressly provided under this License. Any attempt otherwise to copy, modify, sublicense or distribute the Program is void, and will automatically terminate your rights under this License. However, parties who have received copies, or rights, from you under this License will not have their licenses terminated so long as such parties remain in full compliance.</p>
<p> <strong>5</strong>. You are not required to accept this License, since you have not signed it. However, nothing else grants you permission to modify or distribute the Program or its derivative works. These actions are prohibited by law if you do not accept this License. Therefore, by modifying or distributing the Program (or any work based on the Program), you indicate your acceptance of this License to do so, and all its terms and conditions for copying, distributing or modifying the Program or works based on it.</p>
<p><strong>6</strong>. Each time you redistribute the Program (or any work based on the Program), the recipient automatically receives a license from the original licensor to copy, distribute or modify the Program subject to these terms and conditions. You may not impose any further restrictions on the recipients\' exercise of the rights granted herein. You are not responsible for enforcing compliance by third parties to this License.</p>
<p><strong>7</strong>. If, as a consequence of a court judgment or allegation of patent infringement or for any other reason (not limited to patent issues), conditions are imposed on you (whether by court order, agreement or otherwise) that contradict the conditions of this License, they do not excuse you from the conditions of this License. If you cannot distribute so as to satisfy simultaneously your obligations under this License and any other pertinent obligations, then as a consequence you may not distribute the Program at all. For example, if a patent license would not permit royalty-free redistribution of the Program by all those who receive copies directly or indirectly through you, then the only way you could satisfy both it and this License would be to refrain entirely from distribution of the Program.</p>
<p>If any portion of this section is held invalid or unenforceable under any particular circumstance, the balance of the section is intended to apply and the section as a whole is intended to apply in other circumstances.</p>
<p>It is not the purpose of this section to induce you to infringe any patents or other property right claims or to contest validity of any such claims; this section has the sole purpose of protecting the integrity of the free software distribution system, which is implemented by public license practices. Many people have made generous contributions to the wide range of software distributed through that system in reliance on consistent application of that system; it is up to the author/donor to decide if he or she is willing to distribute software through any other system and a licensee cannot impose that choice.</p>
<p> This section is intended to make thoroughly clear what is believed to be a consequence of the rest of this License.</p>
<p> <strong>8</strong>. If the distribution and/or use of the Program is restricted in certain countries either by patents or by copyrighted interfaces, the original copyright holder who places the Program under this License may add an explicit geographical distribution limitation excluding those countries, so that distribution is permitted only in or among countries not thus excluded. In such case, this License incorporates the limitation as if written in the body of this License.</p>
<p> <strong>9</strong>. The Free Software Foundation may publish revised and/or new versions of the General Public License from time to time. Such new versions will be similar in spirit to the present version, but may differ in detail to address new problems or concerns.</p>
<p>Each version is given a distinguishing version number. If the Program specifies a version number of this License which applies to it and "any later version", you have the option of following the terms and conditions either of that version or of any later version published by the Free Software Foundation. If the Program does not specify a version number of this License, you may choose any version ever published by the Free Software Foundation.</p>
<p><strong>10</strong>. If you wish to incorporate parts of the Program into other free programs whose distribution conditions are different, write to the author to ask for permission. For software which is copyrighted by the Free Software Foundation, write to the Free Software Foundation; we sometimes make exceptions for this. Our decision will be guided by the two goals of preserving the free status of all derivatives of our free software and of promoting the sharing and reuse of software generally.</p>
<p><strong>NO WARRANTY</strong></p>
<p><strong>11</strong>. BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING, REPAIR OR CORRECTION.</p>
<p><strong>12</strong>. IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.</p>
<p><strong>END OF TERMS AND CONDITIONS</strong></p>',
];
