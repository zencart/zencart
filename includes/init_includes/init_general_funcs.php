<?php
/**
 * load the system wide functions
 * see  {@link  https://docs.zen-cart.com/dev/code/init_system/} for more details.
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2019 Jul 25 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * General Functions
 */
require(DIR_WS_FUNCTIONS . 'functions_general.php');
require(DIR_WS_FUNCTIONS . 'functions_general_shared.php');
require DIR_WS_FUNCTIONS . 'functions_attributes.php';
require DIR_WS_FUNCTIONS . 'functions_files.php';
require DIR_WS_FUNCTIONS . 'functions_traffic.php';
require DIR_WS_FUNCTIONS . 'functions_strings.php';
/**
 * Database
 */
require(DIR_WS_FUNCTIONS . 'database.php');

require DIR_WS_FUNCTIONS . 'functions_search.php';
require DIR_WS_FUNCTIONS . 'functions_addresses.php';
require(DIR_WS_FUNCTIONS . 'functions_products.php');

require(DIR_WS_FUNCTIONS . 'functions_dates.php');
require DIR_WS_FUNCTIONS . 'functions_urls.php';
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
