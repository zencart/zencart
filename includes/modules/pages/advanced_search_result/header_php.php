<?php
/**
 * Header code file for the Advanced Search Results page
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in v1.6.0 $
 */

// This should be first line of the script:
$zco_notifier->notify('NOTIFY_HEADER_START_ADVANCED_SEARCH_RESULTS');

if (!defined('KEYWORD_FORMAT_STRING')) define('KEYWORD_FORMAT_STRING','keywords');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$error = false;
$missing_one_input = false;

$_GET['keyword'] = trim($_GET['keyword']);

if ( (isset($_GET['keyword']) && (empty($_GET['keyword']) || $_GET['keyword']==HEADER_SEARCH_DEFAULT_TEXT || $_GET['keyword'] == KEYWORD_FORMAT_STRING ) ) &&
(isset($_GET['dfrom']) && (empty($_GET['dfrom']) || ($_GET['dfrom'] == DOB_FORMAT_STRING))) &&
(isset($_GET['dto']) && (empty($_GET['dto']) || ($_GET['dto'] == DOB_FORMAT_STRING))) &&
(isset($_GET['pfrom']) && !is_numeric($_GET['pfrom'])) &&
(isset($_GET['pto']) && !is_numeric($_GET['pto'])) ) {
  $error = true;
  $missing_one_input = true;
  $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
} else {
  $dfrom = '';
  $dto = '';
  $pfrom = '';
  $pto = '';
  $keywords = '';

  if (isset($_GET['dfrom'])) {
    $dfrom = (($_GET['dfrom'] == DOB_FORMAT_STRING) ? '' : $_GET['dfrom']);
  }

  if (isset($_GET['dto'])) {
    $dto = (($_GET['dto'] == DOB_FORMAT_STRING) ? '' : $_GET['dto']);
  }

  if (isset($_GET['pfrom'])) {
    $pfrom = $_GET['pfrom'];
  }

  if (isset($_GET['pto'])) {
    $pto = $_GET['pto'];
  }

  if (isset($_GET['keyword']) && $_GET['keyword'] != HEADER_SEARCH_DEFAULT_TEXT  && $_GET['keyword'] != KEYWORD_FORMAT_STRING) {
    $keywords = $_GET['keyword'];
  }

  $date_check_error = false;
  if (zen_not_null($dfrom)) {
    if (!zen_checkdate($dfrom, DOB_FORMAT_STRING, $dfrom_array)) {
      $error = true;
      $date_check_error = true;

      $messageStack->add_session('search', ERROR_INVALID_FROM_DATE);
    }
  }

  if (zen_not_null($dto)) {
    if (!zen_checkdate($dto, DOB_FORMAT_STRING, $dto_array)) {
      $error = true;
      $date_check_error = true;

      $messageStack->add_session('search', ERROR_INVALID_TO_DATE);
    }
  }

  if (($date_check_error == false) && zen_not_null($dfrom) && zen_not_null($dto)) {
    if (mktime(0, 0, 0, $dfrom_array[1], $dfrom_array[2], $dfrom_array[0]) > mktime(0, 0, 0, $dto_array[1], $dto_array[2], $dto_array[0])) {
      $error = true;

      $messageStack->add_session('search', ERROR_TO_DATE_LESS_THAN_FROM_DATE);
    }
  }

  $price_check_error = false;
  if (zen_not_null($pfrom)) {
    if (!settype($pfrom, 'float')) {
      $error = true;
      $price_check_error = true;

      $messageStack->add_session('search', ERROR_PRICE_FROM_MUST_BE_NUM);
    }
  }

  if (zen_not_null($pto)) {
    if (!settype($pto, 'float')) {
      $error = true;
      $price_check_error = true;

      $messageStack->add_session('search', ERROR_PRICE_TO_MUST_BE_NUM);
    }
  }

  if (($price_check_error == false) && is_float($pfrom) && is_float($pto)) {
    if ($pfrom > $pto) {
      $error = true;

      $messageStack->add_session('search', ERROR_PRICE_TO_LESS_THAN_PRICE_FROM);
    }
  }

  if (zen_not_null($keywords)) {
    if (!zen_parse_search_string(stripslashes($keywords), $search_keywords)) {
      $error = true;

      $messageStack->add_session('search', ERROR_INVALID_KEYWORDS);
    }
  }
}

if (empty($dfrom) && empty($dto) && empty($pfrom) && empty($pto) && empty($keywords)) {
  $error = true;
  // redundant should be able to remove this
  if (!$missing_one_input) {
    $messageStack->add_session('search', ERROR_AT_LEAST_ONE_INPUT);
  }
}

if ($error == true) {
  zen_redirect(zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params(), 'NONSSL', true, false));
}
$breadcrumb->add(NAVBAR_TITLE_1, zen_href_link(FILENAME_ADVANCED_SEARCH));
$breadcrumb->add(NAVBAR_TITLE_2);
$breadcrumb->add(zen_output_string_protected($keywords));

$qb = new ZenCart\QueryBuilder\QueryBuilder($db);
$box = new ZenCart\QueryBuilderDefinitions\definitions\SearchResults($zcRequest, $db);
$paginator = new ZenCart\Paginator\Paginator($zcRequest);
$builder = new ZenCart\QueryBuilder\PaginatorBuilder($zcRequest, $box->getListingQuery(), $paginator);
$box->buildResults($qb, $db, new ZenCart\QueryBuilder\DerivedItemManager, $builder->getPaginator());
$tplVars['listingBox'] = $box->getTplVars();
//print_r($qb->getQuery());
//die();
if ($box->getTotalItemCount() === 0) {
  $messageStack->add_session('search', TEXT_NO_PRODUCTS, 'caution');
  zen_redirect(zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params('action')));
}
// This should be last line of the script:
$zco_notifier->notify('NOTIFY_HEADER_END_ADVANCED_SEARCH_RESULTS', $keywords);
