<?php
/**
 * meta_tags module
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2020 Apr 08 Modified in v1.5.7 $
 */
$meta_tags_over_ride = false;
$metatag_page_name = $current_page_base;
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
// This should be first line of the script:
$zco_notifier->notify('NOTIFY_MODULE_START_META_TAGS', $current_page_base, $metatag_page_name, $meta_tags_over_ride);

// Add tertiary section to site tagline
if (strlen(SITE_TAGLINE) > 1) {
  define('TAGLINE', TERTIARY_SECTION . SITE_TAGLINE);
} else {
  define('TAGLINE', '');
}
$review_on = "";
$keywords_string_metatags = "";
if (!defined('METATAGS_DIVIDER')) define('METATAGS_DIVIDER', ', ');

// Get all top category names for use with web site keywords
$sql = "SELECT cd.categories_name FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.parent_id = 0 AND c.categories_id = cd.categories_id AND cd.language_id='" . (int)$_SESSION['languages_id'] . "' AND c.categories_status=1";
$keywords_metatags = $db->Execute($sql);
while (!$keywords_metatags->EOF) {
  $keywords_string_metatags .= zen_clean_html($keywords_metatags->fields['categories_name']) . METATAGS_DIVIDER;
  $keywords_metatags->MoveNext();
}
$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_BUILDKEYWORDS', CUSTOM_KEYWORDS, $keywords_string_metatags);
define('KEYWORDS', str_replace('"','',zen_clean_html($keywords_string_metatags) . CUSTOM_KEYWORDS));

// if per-page metatags overrides have been defined, use those, otherwise use usual defaults:
if ($metatag_page_name != 'index') {
  if (defined('META_TAG_TITLE_' . strtoupper($metatag_page_name))) define('META_TAG_TITLE', constant('META_TAG_TITLE_' . strtoupper($metatag_page_name)));
  if (defined('META_TAG_DESCRIPTION_' . strtoupper($metatag_page_name))) define('META_TAG_DESCRIPTION', constant('META_TAG_DESCRIPTION_' . strtoupper($metatag_page_name)));
  if (defined('META_TAG_KEYWORDS_' . strtoupper($metatag_page_name))) define('META_TAG_KEYWORDS', constant('META_TAG_KEYWORDS_' . strtoupper($metatag_page_name)));
}

// Get different meta tag values depending on main_page values
switch ($metatag_page_name) {
  case 'advanced_search':
  case 'account_edit':
  case 'account_history':
  case 'account_history_info':
  case 'account_newsletters':
  case 'account_notifications':
  case 'account_password':
  case 'address_book':
  define('META_TAG_TITLE', HEADING_TITLE . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . NAVBAR_TITLE_1 . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . NAVBAR_TITLE_1);
  break;

  case 'address_book_process':
  define('META_TAG_TITLE', NAVBAR_TITLE_ADD_ENTRY . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . NAVBAR_TITLE_ADD_ENTRY . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . NAVBAR_TITLE_ADD_ENTRY);
  break;

  case 'advanced_search_result':
  define('META_TAG_TITLE', NAVBAR_TITLE_2 . ' -> ' . zen_output_string_protected($keywords) . ' ' . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', '');
  define('META_TAG_KEYWORDS', '');
  break;

  case 'password_forgotten':
  define('META_TAG_TITLE', NAVBAR_TITLE_2 . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . NAVBAR_TITLE_2 . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . NAVBAR_TITLE_2);
  break;

  case 'checkout_confirmation':
  case 'checkout_payment':
  case 'checkout_payment_address':
  case 'checkout_shipping':
  case 'checkout_success':
  case 'create_account_success':
  define('META_TAG_TITLE', HEADING_TITLE . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . HEADING_TITLE . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . HEADING_TITLE);
  break;

  case ($this_is_home_page):
  define('META_TAG_TITLE', (defined('HOME_PAGE_TITLE') && HOME_PAGE_TITLE != '' ? HOME_PAGE_TITLE : (defined('NAVBAR_TITLE') ? NAVBAR_TITLE . PRIMARY_SECTION : '') . TITLE . TAGLINE));
  define('META_TAG_DESCRIPTION', (defined('HOME_PAGE_META_DESCRIPTION') && HOME_PAGE_META_DESCRIPTION != '') ? HOME_PAGE_META_DESCRIPTION : TITLE . PRIMARY_SECTION . (defined('NAVBAR_TITLE') ? NAVBAR_TITLE : '' ) . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', (defined('HOME_PAGE_META_KEYWORDS') && HOME_PAGE_META_KEYWORDS != '') ? HOME_PAGE_META_KEYWORDS : KEYWORDS . METATAGS_DIVIDER . (defined('NAVBAR_TITLE') ? NAVBAR_TITLE : '' ) );
  break;

  case 'index':
  // bof: categories meta tags
  // run custom categories meta tags
  $sql = "SELECT * FROM " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " mcd WHERE mcd.categories_id = '" . (int)$current_category_id . "' AND mcd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  $category_metatags = $db->Execute($sql);
  if (!$category_metatags->EOF) {
    define('META_TAG_TITLE', str_replace('"','',$category_metatags->fields['metatags_title']));
    define('META_TAG_DESCRIPTION', str_replace('"','',$category_metatags->fields['metatags_description']));
    define('META_TAG_KEYWORDS', str_replace('"','',$category_metatags->fields['metatags_keywords']));
  } else {
    // build categories meta tags
    // eof: categories meta tags
    if ($category_depth == 'nested') {
      $sql = "SELECT cd.categories_name FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id AND cd.categories_id = '" . (int)$current_category_id . "' AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND c.categories_status=1";
      $category_metatags = $db->Execute($sql);
      if ($category_metatags->EOF) {
        $meta_tags_over_ride = true;
      } else {
        define('META_TAG_TITLE', str_replace('"','', zen_clean_html($category_metatags->fields['categories_name']) . PRIMARY_SECTION . TITLE . TAGLINE));
        define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . PRIMARY_SECTION . zen_clean_html($category_metatags->fields['categories_name']) . SECONDARY_SECTION . KEYWORDS));
        define('META_TAG_KEYWORDS', str_replace('"','',KEYWORDS . METATAGS_DIVIDER . zen_clean_html($category_metatags->fields['categories_name'])));
      } // EOF
    } elseif ($category_depth == 'products') {
      if (isset($_GET['manufacturers_id']) || ((isset($_GET['filter_id']) && $_GET['filter_id'] > 0) && isset($_GET['cPath'])) ) {
        if ((isset($_GET['filter_id']) && isset($_GET['cPath'])) ) {
          $include_manufacturers_id = $_GET['filter_id'];
        } else {
          $include_manufacturers_id = $_GET['manufacturers_id'];
        }
        $sql = "SELECT manufacturers_name FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = '" . (int)$include_manufacturers_id . "'";
        $manufacturer_metatags = $db->Execute($sql);
        if ($manufacturer_metatags->EOF) {
          $meta_tags_over_ride = true;
        } else {
          define('META_TAG_TITLE', str_replace('"','',$manufacturer_metatags->fields['manufacturers_name'] . PRIMARY_SECTION . TITLE . TAGLINE));
          define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . PRIMARY_SECTION . $manufacturer_metatags->fields['manufacturers_name'] . SECONDARY_SECTION . KEYWORDS));
          define('META_TAG_KEYWORDS', str_replace('"','', $manufacturer_metatags->fields['manufacturers_name'] . METATAGS_DIVIDER . KEYWORDS));
        } // EOF
      } else {
        $sql = "SELECT cd.categories_name FROM " . TABLE_CATEGORIES . ' c, ' . TABLE_CATEGORIES_DESCRIPTION . " cd WHERE c.categories_id = cd.categories_id AND cd.categories_id = '" . (int)$current_category_id . "' AND cd.language_id = '" . (int)$_SESSION['languages_id'] . "' AND c.categories_status=1";
        $category_metatags = $db->Execute($sql);
        if ($category_metatags->EOF) {
          $meta_tags_over_ride = true;
        } else {
          define('META_TAG_TITLE', str_replace('"','', zen_clean_html($category_metatags->fields['categories_name']) . PRIMARY_SECTION . TITLE . TAGLINE));
          define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . PRIMARY_SECTION . zen_clean_html($category_metatags->fields['categories_name']) . SECONDARY_SECTION . KEYWORDS));
          define('META_TAG_KEYWORDS', str_replace('"','',KEYWORDS . METATAGS_DIVIDER . zen_clean_html($category_metatags->fields['categories_name'])));
        } // EOF
      }
    } else {
      if (isset($_GET['manufacturers_id'])) {
        $sql = "SELECT manufacturers_name FROM " . TABLE_MANUFACTURERS . " WHERE manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
        $manufacturer_metatags = $db->Execute($sql);
        if ($manufacturer_metatags->EOF) {
          define('META_TAG_TITLE', TITLE . TAGLINE);
          define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . str_replace(array("'",'"'),'',strip_tags(HEADING_TITLE)) . SECONDARY_SECTION . KEYWORDS);
          define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . str_replace(array("'",'"'),'',strip_tags(HEADING_TITLE)));
        } else {
          define('META_TAG_TITLE', str_replace('"','', $manufacturer_metatags->fields['manufacturers_name'] . PRIMARY_SECTION . TITLE . TAGLINE));
          define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . PRIMARY_SECTION . $manufacturer_metatags->fields['manufacturers_name'] . SECONDARY_SECTION . KEYWORDS));
          define('META_TAG_KEYWORDS', str_replace('"','', $manufacturer_metatags->fields['manufacturers_name'] . METATAGS_DIVIDER . KEYWORDS));
        }
      } else {
        // nothing custom main page
        $meta_tags_over_ride = true;
      }
    }
  } // custom meta tags
  break;
  // eof: categories meta tags

  case 'popup_image':
  $meta_products_name = '';
  if (isset($products_values->fields['products_name'])) {
    $meta_products_name = str_replace('"','',zen_clean_html($products_values->fields['products_name']));
  }
  define('META_TAG_TITLE', $meta_products_name . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . $meta_products_name . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . $meta_products_name);
  break;

  case 'popup_image_additional':
  $meta_products_name = '';
  if (isset($products_values->fields['products_name'])) {
    $meta_products_name = str_replace('"','',zen_clean_html($products_values->fields['products_name']));
  }
  define('META_TAG_TITLE', $meta_products_name . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . $meta_products_name . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . $meta_products_name);
  break;

  case 'popup_search_help':
  define('META_TAG_TITLE', HEADING_SEARCH_HELP . PRIMARY_SECTION . TITLE . TAGLINE);
  define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . HEADING_SEARCH_HELP . SECONDARY_SECTION . KEYWORDS);
  define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . HEADING_SEARCH_HELP);
  break;

  // unless otherwise required product_reviews uses the same settings as product_reviews_info and other _info pages
  case 'product_reviews':
  // unless otherwise required product_reviews_info uses the same settings as reviews and other _info pages
  case 'product_reviews_info':
  $review_on = META_TAGS_REVIEW;
  //  case 'product_info':
  case (strstr($_GET['main_page'], 'product_') or strstr($_GET['main_page'], 'document_')):
  if (empty($_GET['products_id'])) {
    $_GET['products_id'] = 0;
  }

  $sql= "SELECT pd.products_name, p.products_model, p.products_price_sorter, p.products_tax_class_id,
                                      p.metatags_title_status, p.metatags_products_name_status, p.metatags_model_status,
                                      p.products_id, p.metatags_price_status, p.metatags_title_tagline_status,
                                      pd.products_description, p.product_is_free, p.product_is_call,
                                      mtpd.metatags_title, mtpd.metatags_keywords, mtpd.metatags_description
                              FROM (" . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd) LEFT JOIN " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " mtpd ON mtpd.products_id = p.products_id AND mtpd.language_id = '" . (int)$_SESSION['languages_id'] . "'
                              WHERE p.products_id = '" . (int)$_GET['products_id'] . "'
                              AND p.products_id = pd.products_id
                              AND pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";

  $product_info_metatags = $db->Execute($sql);
  if ($product_info_metatags->EOF) {
    $meta_tags_over_ride = true;
  } else {
    // custom meta tags per product
    if (!empty($product_info_metatags->fields['metatags_keywords']) or !empty($product_info_metatags->fields['metatags_description'])) {
      $meta_products_name = '';
      $meta_products_price = '';
      $metatags_keywords = '';

      $meta_products_price = ($product_info_metatags->fields['metatags_price_status'] == '1' ? SECONDARY_SECTION . ($product_info_metatags->fields['products_price_sorter'] > 0 ? $currencies->display_price($product_info_metatags->fields['products_price_sorter'], zen_get_tax_rate($product_info_metatags->fields['products_tax_class_id'])) : SECONDARY_SECTION . META_TAG_PRODUCTS_PRICE_IS_FREE_TEXT) : '');

      $meta_products_name .= ($product_info_metatags->fields['metatags_products_name_status'] == '1' ? $product_info_metatags->fields['products_name'] : '');
      $meta_products_name .= ($product_info_metatags->fields['metatags_title_status'] == '1' ? ' ' . $product_info_metatags->fields['metatags_title'] : '');
      $meta_products_name .= ($product_info_metatags->fields['metatags_model_status'] == '1' ? ' [' . $product_info_metatags->fields['products_model'] . ']' : '');
      if (zen_check_show_prices() == true) {
        $meta_products_name .= $meta_products_price;
      }
      $meta_products_name .= ($product_info_metatags->fields['metatags_title_tagline_status'] == '1' ? PRIMARY_SECTION . TITLE . TAGLINE : '');

      if (!empty($product_info_metatags->fields['metatags_description'])) {
        // use custom description
        $metatags_description = $product_info_metatags->fields['metatags_description'];
      } else {
        // no custom description defined use product_description
        $metatags_description = zen_truncate_paragraph(strip_tags(stripslashes($product_info_metatags->fields['products_description'])), MAX_META_TAG_DESCRIPTION_LENGTH);
      }

      $metatags_description = zen_clean_html($metatags_description);

      if (!empty($product_info_metatags->fields['metatags_keywords'])) {
        // use custom keywords
        $metatags_keywords = $product_info_metatags->fields['metatags_keywords'] . METATAGS_DIVIDER . CUSTOM_KEYWORDS;  // CUSTOM skips categories
      } else {
        // no custom keywords defined use product_description
        $metatags_keywords = KEYWORDS . METATAGS_DIVIDER . $meta_products_name . METATAGS_DIVIDER;
      }

      define('META_TAG_TITLE', str_replace('"','',zen_clean_html($review_on . $meta_products_name)));
      define('META_TAG_DESCRIPTION', str_replace('"','',zen_clean_html($metatags_description . ' ')));
      define('META_TAG_KEYWORDS', str_replace('"','',zen_clean_html($metatags_keywords)));  // KEYWORDS and CUSTOM_KEYWORDS are added above

    } else {
      // build un-customized meta tag
      if (META_TAG_INCLUDE_PRICE == '1' and !strstr($_GET['main_page'], 'document_general')) {
        if ($product_info_metatags->fields['product_is_free'] != '1') {
          if (zen_check_show_prices() == true) {
            $meta_products_price = zen_get_products_actual_price($product_info_metatags->fields['products_id']);
            $prod_is_call_and_no_price = ($product_info_metatags->fields['product_is_call'] == '1' && $meta_products_price == 0);
            $meta_products_price = (!$prod_is_call_and_no_price ? SECONDARY_SECTION . $currencies->display_price($meta_products_price, zen_get_tax_rate($product_info_metatags->fields['products_tax_class_id'])) : '');
          }
        } else {
          $meta_products_price = SECONDARY_SECTION . META_TAG_PRODUCTS_PRICE_IS_FREE_TEXT;
        }
      } else {
        $meta_products_price = '';
      }

      if (META_TAG_INCLUDE_MODEL == '1' && zen_not_null($product_info_metatags->fields['products_model'])) {
        $meta_products_name = $product_info_metatags->fields['products_name'] . ' [' . $product_info_metatags->fields['products_model'] . ']';
      } else {
        $meta_products_name = $product_info_metatags->fields['products_name'];
      }
      $meta_products_name = zen_clean_html($meta_products_name);

      $meta_products_description = zen_truncate_paragraph(strip_tags(stripslashes($product_info_metatags->fields['products_description'])), MAX_META_TAG_DESCRIPTION_LENGTH);

      $meta_products_description = zen_clean_html($meta_products_description);

      define('META_TAG_TITLE', str_replace('"','',$review_on . $meta_products_name . $meta_products_price . PRIMARY_SECTION . TITLE . TAGLINE));
      define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . ' ' . $meta_products_name . SECONDARY_SECTION . $meta_products_description . ' '));
      define('META_TAG_KEYWORDS', str_replace('"','',$meta_products_name . METATAGS_DIVIDER . KEYWORDS));

    } // CUSTOM META TAGS
  } // EOF
  break;

  case 'product_reviews_info_OFF':
  $sql = "SELECT rd.reviews_text, r.reviews_rating, r.reviews_id, r.customers_name, p.products_id, p.products_price, p.products_tax_class_id, p.products_model, pd.products_name, p.product_is_free FROM " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd WHERE r.reviews_id = '" . (int)$_GET['reviews_id'] . "' AND r.reviews_id = rd.reviews_id AND rd.languages_id = '" . (int)$_SESSION['languages_id'] . "' AND r.products_id = p.products_id AND p.products_status = 1 AND p.products_id = pd.products_id AND pd.language_id = '". (int)$_SESSION['languages_id'] . "'";
  $review_metatags = $db->Execute($sql);
  if ($review_metatags->EOF) {
    $meta_tags_over_ride = true;
  } else {
    if (META_TAG_INCLUDE_PRICE == '1') {
      if ($review_metatags->fields['product_is_free'] != '1') {
        $meta_products_price = zen_get_products_actual_price($review_metatags->fields['products_id']);
        $meta_products_price = SECONDARY_SECTION . $currencies->display_price($meta_products_price, zen_get_tax_rate($review_metatags->fields['products_tax_class_id']));
      } else {
        $meta_products_price = SECONDARY_SECTION . META_TAG_PRODUCTS_PRICE_IS_FREE_TEXT;
      }
    } else {
      $meta_products_price = '';
    }

    if (zen_not_null($review_metatags->fields['products_model'])) {
      $meta_products_name = $review_metatags->fields['products_name'] . ' [' . $review_metatags->fields['products_model'] . ']';
    } else {
      $meta_products_name = $review_metatags->fields['products_name'];
    }

    $meta_products_name = zen_clean_html($meta_products_name);

    $review_text_metatags = substr(strip_tags(stripslashes($review_metatags->fields['reviews_text'])), 0, 60);
    $reviews_rating_metatags = SUB_TITLE_RATING . ' ' . sprintf(TEXT_OF_5_STARS, $review_metatags->fields['reviews_rating']);
    define('META_TAG_TITLE', str_replace('"','',$meta_products_name . $meta_products_price . PRIMARY_SECTION . TITLE . TERTIARY_SECTION . NAVBAR_TITLE));
    define('META_TAG_DESCRIPTION', str_replace('"','',TITLE . PRIMARY_SECTION . NAVBAR_TITLE . SECONDARY_SECTION . $meta_products_name . SECONDARY_SECTION . $review_metatags->fields['customers_name'] . SECONDARY_SECTION . $review_text_metatags . ' ' . SECONDARY_SECTION . $reviews_rating_metatags));
    define('META_TAG_KEYWORDS', str_replace('"','',KEYWORDS . METATAGS_DIVIDER . $meta_products_name . METATAGS_DIVIDER . $review_metatags->fields['customers_name'] . METATAGS_DIVIDER . $reviews_rating_metatags));
  } // EOF
  break;
// EZ-Pages:
  case 'page':
  $ezpage_id = (int)$_GET['id'];
  $chapter_id = isset($_GET['chapter']) ? (int)$_GET['chapter'] : 0;
  if (defined('META_TAG_TITLE_EZPAGE_'.$ezpage_id)) define('META_TAG_TITLE', constant('META_TAG_TITLE_EZPAGE_'.$ezpage_id));
  if (defined('META_TAG_DESCRIPTION_EZPAGE_'.$ezpage_id)) define('META_TAG_DESCRIPTION', constant('META_TAG_DESCRIPTION_EZPAGE_'.$ezpage_id));
  if (defined('META_TAG_KEYWORDS_EZPAGE_'.$ezpage_id)) define('META_TAG_KEYWORDS', constant('META_TAG_KEYWORDS_EZPAGE_'.$ezpage_id));
// NO "break" here. Allow defaults if not overridden at the per-page level
  default:
    $metatags_title = (defined('NAVBAR_TITLE') ? NAVBAR_TITLE . PRIMARY_SECTION : '') . TITLE . TAGLINE;
    $metatags_description = TITLE . (defined('NAVBAR_TITLE') ? PRIMARY_SECTION . NAVBAR_TITLE : '') . SECONDARY_SECTION . KEYWORDS;
    $metatags_keywords = KEYWORDS . METATAGS_DIVIDER . (defined('NAVBAR_TITLE') ? NAVBAR_TITLE : '');
    $zco_notifier->notify('NOTIFY_MODULE_META_TAGS_UNSPECIFIEDPAGE', $current_page_base, $metatag_page_name, $meta_tags_over_ride, $metatags_title, $metatags_description, $metatags_keywords);
    if (false===$meta_tags_over_ride) {
      define('META_TAG_TITLE', (defined('NAVBAR_TITLE') ? NAVBAR_TITLE . PRIMARY_SECTION : '') . TITLE . TAGLINE);
      define('META_TAG_DESCRIPTION', TITLE . PRIMARY_SECTION . (defined('NAVBAR_TITLE') ? NAVBAR_TITLE : '' ) . SECONDARY_SECTION . KEYWORDS);
      define('META_TAG_KEYWORDS', KEYWORDS . METATAGS_DIVIDER . (defined('NAVBAR_TITLE') ? NAVBAR_TITLE : '' ) );
    }
}

$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_OVERRIDE', $metatag_page_name, $meta_tags_over_ride, $metatags_title, $metatags_description, $metatags_keywords);

// meta tags override due to 404, missing products_id, cPath or other EOF issues
if ($meta_tags_over_ride == true) {
  define('META_TAG_TITLE', $metatags_title);
  define('META_TAG_DESCRIPTION', $metatags_description);
  define('META_TAG_KEYWORDS', $metatags_keywords);
}

// This should be last line of the script:
$zco_notifier->notify('NOTIFY_MODULE_END_META_TAGS');
