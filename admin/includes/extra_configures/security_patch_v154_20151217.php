<?php
/**
 * @package patches
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: security_patch_v154_20151217.php $
 */
/**
 * Security Patch v154 20151217
 *
 * NOTE: This patch is only for v1.5.x and older.
 * Install this file in /admin/includes/extra_configures/ folder.
 * Note: This file is applicable for v155 despite the filename mentioning v154 ... this is to encourage v154 users to install it even if not using v155 yet
 */

$filedirRegex = '~[^0-9a-z\.!@#\$%^&\( )`_+\-' . preg_quote(DIRECTORY_SEPARATOR) . '\~]~i';
$prodNameRegex = '~(<\/?scri|on(load|mouse|error|read|key)=|= ?(\(|")|<!)~i';
$prodDescRegex = '~(load=|= ?\(|<![^-])~i';
$urlRegex = '~([^a-z0-9\'!#$&%@();:/=?_\~\[\]-]|[><])~i';
$alphaNumDashUnderscore = '/[^a-z0-9_-]/i';

$group = array('img_dir', 'products_previous_image', 'products_image_manual', 'products_attributes_filename');
foreach($group as $key) {
  if (isset($_POST[$key])) $_POST[$key] = preg_replace($filedirRegex, '', $_POST[$key]);
}

$group = array('handler', 'action', 'product_attribute_is_free', 'attributes_default', 'attributes_price_base_included', 'products_attribute_maxdays',
               'products_filter', 'module', 'page', 'attribute_page', 'cPath');
foreach($group as $key) {
  if (isset($_POST[$key])) $_POST[$key] = preg_replace($alphaNumDashUnderscore, '', $_POST[$key]);
  if (isset($_GET[$key])) $_GET[$key] = preg_replace($alphaNumDashUnderscore, '', $_GET[$key]);
}

$group = array('title', 'coupon_name');
foreach($group as $key) {
  if (isset($_POST[$key])) $_POST[$key] = preg_replace($prodNameRegex, '', $_POST[$key]);
  if (isset($_GET[$key])) $_GET[$key] = preg_replace($prodNameRegex, '', $_GET[$key]);
}

if (isset($_POST['banners_title'])) {
  $_POST['banners_title'] = preg_replace($prodNameRegex, '', $_POST['banners_title']);
}
if (isset($_POST['coupon_code'])) {
  $_POST['coupon_code'] = preg_replace($prodNameRegex, '', $_POST['coupon_code']);
}

$group = array('current_master_categories_id', 'categories_id', 'cID', 'pID', 'attributes_id', 'id', 'padID', 'coupon_uses_coupon', 'coupon_uses_user', 'coupon_zone_restriction');
foreach($group as $key) {
  if (isset($_POST[$key])) $_POST[$key] = (int)$_POST[$key];
  if (isset($_GET[$key])) $_GET[$key] = (int)$_GET[$key];
}

if (isset($_POST['products_url'])) {
  foreach($_POST['products_url'] as $key => $value) {
    if (false === filter_var($_POST['products_url'][$key], FILTER_SANITIZE_URL)) {
      $_POST['products_url'][$key] = preg_replace($urlRegex, '', $_POST['products_url'][$key]);
    }
  }
}

if (isset($_POST['products_name'])) {
  if(is_array($_POST['products_name'])) {
    foreach ($_POST['products_name'] as $key => $value) {
      $_POST['products_name'][$key] = preg_replace($prodNameRegex, '', $_POST['products_name'][$key]);
    }
  } else {
    $_POST['products_name'] = preg_replace($prodNameRegex, '', $_POST['products_name']);
  }
}

if (isset($_POST['products_description'])) {
  foreach($_POST['products_description'] as $key => $value) {
    $_POST['products_description'][$key] = preg_replace($prodDescRegex, '', $_POST['products_description'][$key]);
  }
}

if (isset($_POST['metatags_title'])) {
  foreach($_POST['metatags_title'] as $key => $value) {
    $_POST['metatags_title'][$key] = htmlspecialchars($_POST['metatags_title'][$key], ENT_COMPAT, 'utf-8', FALSE);
  }
}

if (isset($_POST['metatags_keywords'])) {
  foreach($_POST['metatags_keywords'] as $key => $value) {
    $_POST['metatags_keywords'][$key] = htmlspecialchars($_POST['metatags_keywords'][$key], ENT_COMPAT, 'utf-8', FALSE);
  }
}

if (isset($_POST['metatags_description'])) {
  foreach($_POST['metatags_description'] as $key => $value) {
    $_POST['metatags_description'][$key] = htmlspecialchars($_POST['metatags_description'][$key], ENT_COMPAT, 'utf-8', FALSE);
  }
}

if (isset($_POST['type_name'])) {
  $_POST['type_name'] = htmlspecialchars($_POST['type_name'], ENT_COMPAT, 'utf-8', FALSE);
}

if (isset($_POST['coupon_desc'])) {
  foreach($_POST['coupon_desc'] as $key => $value) {
    $_POST['coupon_desc'][$key] = preg_replace($prodDescRegex, '', $_POST['coupon_desc'][$key]);
  }
}
if (isset($_POST)) {
  foreach($_POST as $key => $value) {
    if (preg_match('~[>/<]~', $key)) unset($_POST[$key]);
  }
}
if (isset($_GET)) {
  foreach($_GET as $key => $value) {
    if (preg_match('~[>/<]~', $key)) unset($_GET[$key]);
  }
}
if (isset($_POST['customers_email_address'])) {
  $_POST['customers_email_address'] = filter_input(INPUT_POST, 'customers_email_address', FILTER_SANITIZE_EMAIL);
}
if (isset($_GET['customers_email_address'])) {
  $_GET['customers_email_address'] = filter_input(INPUT_GET, 'customers_email_address', FILTER_SANITIZE_EMAIL);
}
if (isset($_POST['coupon_min_order'])) {
  $_POST['coupon_min_order'] = preg_replace('/[^a-z0-9_,\.\-]/i', '', $_POST['coupon_min_order']);
}
