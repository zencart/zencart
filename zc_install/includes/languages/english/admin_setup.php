<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: admin_setup.php 19537 2011-09-20 17:14:44Z drbyte $
 */
/**
 * defining language components for the page
 */
  define('TEXT_PAGE_HEADING', 'Zen Cart&reg; Setup - Administrator Account Setup');
  define('SAVE_ADMIN_SETTINGS', 'Save Admin Settings');//this comes before TEXT_MAIN
  define('TEXT_MAIN', "To administer settings in your Zen Cart&reg; shop, you need an Administrative account.  Please select an administrator's name, and password, and enter an email address for reset passwords to be sent to.  Enter and check the information carefully and press <em>".SAVE_ADMIN_SETTINGS.'</em> when you are done.');
  define('ADMIN_INFORMATION', 'Administrator Information');
  define('ADMIN_USERNAME', 'Administrator\'s Username');
  define('ADMIN_USERNAME_INSTRUCTION', 'Enter the username to be used for your Zen Cart&reg; administrator account.');
  define('ADMIN_PASS', 'TEMPORARY Admin Password');
  define('ADMIN_PASS_INSTRUCTION', 'Enter a <strong>TEMPORARY</strong> password to be used for your Zen Cart&reg; administrator account. You will be asked to change this password at first login.<br />Your password <strong>must contain both NUMBERS and LETTERS and minimum 7 characters.</strong>');
  define('ADMIN_PASS_CONFIRM', 'Confirm temporary Admin Password');
  define('ADMIN_PASS_CONFIRM_INSTRUCTION', 'Re-enter the temporary password.');
  define('ADMIN_EMAIL', 'Administrator\'s Email');
  define('ADMIN_EMAIL_INSTRUCTION', 'Enter the email address to be used for your Zen Cart&reg; administrator account. This will be used for testing newsletter emails and for sending password resets, etc.');
  define('UPGRADE_DETECTION','Upgrade Detection');
  define('UPGRADE_INSTRUCTION_TITLE','Check for Zen Cart&reg; updates when logging into Admin');
  define('UPGRADE_INSTRUCTION_TEXT','This will attempt to talk to the live Zen Cart&reg; versioning server to determine if an upgrade is available or not. If an update is available, a message will appear in admin.  It will NOT automatically APPLY any upgrades.<br />You can override this later in Admin->Config->My Store->Check if version update is available.');
