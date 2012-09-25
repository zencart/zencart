<?php
define('SERVER_NAME', 'localhost');
define('BASE_URL', 'localhost');
define('DB_PREFIX', '');
define('DB_HOST', 'localhost');
define('DB_USER', 'dbuser');
define('DB_PASS', 'password');
define('DB_DBNAME', 'zencart');
define('DIR_FS_ADMIN', '~/public_html/v15/admin/');
define('DIR_FS_CATALOG', '~/public_html/v15/');
define('SELENIUM_BROWSER', '*chrome /Applications/Firefox3.app/Contents/MacOS/firefox-bin');

define('WEBTEST_STORE_NAME', 'Selenium Test Store on ' . BASE_URL);
define('WEBTEST_STORE_OWNER', 'Selenium Test ' . BASE_URL);
define('WEBTEST_STORE_OWNER_EMAIL', 'noreply@' . SERVER_NAME);
define('WEBTEST_ADMIN_EMAIL', 'noreply@'. SERVER_NAME);

define('WEBTEST_DEFAULT_CUSTOMER_EMAIL', 'test1@'. SERVER_NAME);
define('WEBTEST_DEFAULT_CUSTOMER_PASSWORD', 'password');
define('WEBTEST_UK_CUSTOMER_EMAIL', 'testuk@'. SERVER_NAME);
define('WEBTEST_UK_CUSTOMER_PASSWORD', 'password');
define('WEBTEST_CANADA_CUSTOMER_EMAIL', 'testcanada@'. SERVER_NAME);
define('WEBTEST_CANADA_CUSTOMER_PASSWORD', 'password');

define('WEBTEST_ADMIN_NAME_INSTALL', 'Admin');
define('WEBTEST_ADMIN_PASSWORD_INSTALL', 'adminPassTemp99');
define('WEBTEST_ADMIN_PASSWORD_INSTALL_1', 'adminPass99');
define('WEBTEST_ADMIN_PASSWORD_INSTALL_SSL', 'adminPass909');

define('WEBTEST_USE_SMTP', false);
define('WEBTEST_EMAIL_SMTPAUTH_MAILBOX', '');
define('WEBTEST_EMAIL_SMTPAUTH_MAIL_SERVER', '');
define('WEBTEST_EMAIL_SMTPAUTH_PASSWORD', '');
define('WEBTEST_EMAIL_SMTPAUTH_MAIL_SERVER_PORT', '');
define('WEBTEST_EMAIL_LINEFEED', 'CRLF');

//define('SCREENSHOT_PATH', realpath(dirname(__FILE__)) . '/');



$file_contents = file_get_contents(realpath(dirname(__FILE__)) . '/../../../includes/dist-configure.php');
chmod(realpath(dirname(__FILE__)) . '/../../../admin/includes/configure.php', 0777);
chmod(realpath(dirname(__FILE__)) . '/../../../includes/configure.php', 0777);
$fp = fopen(realpath(dirname(__FILE__)) . '/../../../includes/configure.php', 'w');
if ($fp)
{
  fputs($fp, $file_contents);
  fclose($fp);
}
