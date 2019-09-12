<?php
/**
 * load the system wide functions
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * @package initSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: Author: DrByte  Tue Apr 1 15:45:22 2014 -0400 Modified in v1.5.3 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * General Functions
 */
require(DIR_WS_FUNCTIONS . 'functions_general.php');
/**
 * html_output functions (href_links, input types etc)
 */
require(DIR_WS_FUNCTIONS . 'html_output.php');
/**
 * basic email functions
 */
require(DIR_WS_FUNCTIONS . 'functions_email.php');
/**
 * EZ-Pages functions
 */
require(DIR_WS_FUNCTIONS . 'functions_ezpages.php');
/**
 * require the plugin support functions
 */
require(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'plugin_support.php');
/**
 * require the password crypto functions
 */
require(DIR_WS_FUNCTIONS . 'password_funcs.php');
/**
 * User Defined Functions
 */
include(DIR_WS_MODULES . 'extra_functions.php');
