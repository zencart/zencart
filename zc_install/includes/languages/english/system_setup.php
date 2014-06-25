<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: system_setup.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('SAVE_SYSTEM_SETTINGS', 'Save System Settings'); //this comes before TEXT_MAIN
  define('TEXT_MAIN', "We will now setup the Zen Cart&reg; System environment.  Please carefully review each setting, and change if necessary to suit your directory layout. Then click on <em>".SAVE_SYSTEM_SETTINGS.'</em> to continue.');
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - System Setup');
  define('SERVER_SETTINGS', 'Server/Site Settings');
  define('PHYSICAL_PATH', 'Physical Path To Zen Cart&reg;');
  define('PHYSICAL_PATH_INSTRUCTION', 'Physical Path to your<br />Zen Cart&reg; directory.<br />Leave no trailing slash.');
  define('VIRTUAL_HTTP_PATH', 'URL to your Zen Cart&reg; store');
  define('VIRTUAL_HTTP_PATH_INSTRUCTION', 'URL to your Zen Cart&reg; store.<br />Leave no trailing slash.');
  define('VIRTUAL_HTTPS_PATH', 'HTTPS Server URL');
  define('VIRTUAL_HTTPS_PATH_INSTRUCTION', 'Full Virtual Path to your<br />secure Zen Cart&reg; directory.<br />Leave no trailing slash.');
  define('VIRTUAL_HTTPS_SERVER', 'HTTPS Domain');
  define('VIRTUAL_HTTPS_SERVER_INSTRUCTION', 'Virtual server for your<br />secure Zen Cart&reg; directory.<br />Leave no trailing slash.');
  define('TEXT_SSL_INTRO', '<strong>Do you already have an SSL Certificate? If so, enter the details below.</strong> If this is your first install, the supplied values are *only best-guesses*. Please verify the information with your hosting company if you are unsure of the correct details.');
  define('TEXT_SSL_WARNING', 'If your SSL certificate is already working, choose your SSL settings below. <br /><strong>DO NOT enable SSL here if you do not already have SSL enabled on your hosting account.</strong> If you enable SSL but the SSL address you provide does not work, you will not be able to access your admin site nor log in to your store. You can activate SSL later by editing settings in your configure.php file.');
  define('SSL_OPTIONS', 'SSL Details');
  define('ENABLE_SSL', 'Enable SSL');
  define('ENABLE_SSL_INSTRUCTION', 'Would you like to enable Secure Sockets Layer in Customer area?<br />Leave this set to NO unless you\'re SURE you have SSL working.');
  define('ENABLE_SSL_ADMIN', 'Enable SSL in Admin Area');
  define('ENABLE_SSL_ADMIN_INSTRUCTION', 'Would you like to enable Secure Sockets Layer for Admin areas?<br />
Leave this set to NO unless you\'re SURE you have SSL working.');
  define('REDISCOVER', 'Redetect defaults for this host');

