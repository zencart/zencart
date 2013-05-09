<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config_checkup.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('TEXT_MAIN', '<h2>Please fix your configuration files</h2><p>Your configure.php files cannot be validated. This means that they most likely do not contain valid information.</p>');
  define('TEXT_EXPLANATION2', '<p>After collecting information from you, we tried to write the collected information to the configure.php files on your server. You are seeing this screen because that process was unsuccessful. Thus, you will likely have to set their contents manually.</p>');
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - Configuration Checkup');
  define('TEXT_CONFIG_FILES', 'Configuration Settings - configure.php files');
  define('TEXT_CONFIG_INSTRUCTIONS', 'You may use your computer clipboard to copy-and-paste the appropriate content using the following boxes.  Click in the box, copy to your clipboard, open the appropriate configure.php file using your text editor, paste the clipboard contents into the file, save, upload. Repeat for the other file.<br /><br />When finished, click on the "Re-Check Files" button below to re-run the validation.');

  define('TEXT_CATALOG_CONFIGFILE', '/includes/configure.php');
  define('TEXT_ADMIN_CONFIGFILE', '/admin/includes/configure.php');

  define('CONTINUE_BUTTON', 'Ignore and Continue');
  define('RECHECK', 'Re-Check Files');
