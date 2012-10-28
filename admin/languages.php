<?php
/**
 * @package admin
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: languages.php 19330 2011-08-07 06:32:56Z drbyte $
 */

  require('includes/application_top.php');
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  if (zen_not_null($action)) {
    switch ($action) {
      case 'insert':
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = zen_db_prepare_input((int)$_POST['sort_order']);
        $check = $db->Execute("select * from " . TABLE_LANGUAGES . " where code = '" . $code . "'");
        if ($check->RecordCount() > 0) {
          $messageStack->add(ERROR_DUPLICATE_LANGUAGE_CODE, 'error');
        } else {

          $db->Execute("insert into " . TABLE_LANGUAGES . "
                        (name, code, image, directory, sort_order)
                        values ('" . zen_db_input($name) . "', '" . zen_db_input($code) . "',
                                '" . zen_db_input($image) . "', '" . zen_db_input($directory) . "',
                                '" . zen_db_input($sort_order) . "')");

          $insert_id = $db->Insert_ID();

          // set default, if selected
          if (isset($_POST['default']) && ($_POST['default'] == 'on')) {
            $db->Execute("update " . TABLE_CONFIGURATION . "
                          set configuration_value = '" . zen_db_input($code) . "'
                          where configuration_key = 'DEFAULT_LANGUAGE'");
          }

// create additional categories_description records
          $categories = $db->Execute("select c.categories_id, cd.categories_name,
                                      categories_description
                                      from " . TABLE_CATEGORIES . " c
                                      left join " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                      on c.categories_id = cd.categories_id
                                      where cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$categories->EOF) {
            $db->Execute("insert into " . TABLE_CATEGORIES_DESCRIPTION . "
                          (categories_id, language_id, categories_name,
                          categories_description)
                          values ('" . (int)$categories->fields['categories_id'] . "', '" . (int)$insert_id . "',
                                  '" . zen_db_input($categories->fields['categories_name']) . "',
                                  '" . zen_db_input($categories->fields['categories_description']) . "')");
            $categories->MoveNext();
          }

// create additional products_description records
          $products = $db->Execute("select p.products_id, pd.products_name, pd.products_description,
                                           pd.products_url
                                    from " . TABLE_PRODUCTS . " p
                                    left join " . TABLE_PRODUCTS_DESCRIPTION . " pd
                                    on p.products_id = pd.products_id
                                    where pd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$products->EOF) {
            $db->Execute("insert into " . TABLE_PRODUCTS_DESCRIPTION . "
                        (products_id, language_id, products_name, products_description, products_url)
                        values ('" . (int)$products->fields['products_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($products->fields['products_name']) . "',
                                '" . zen_db_input($products->fields['products_description']) . "',
                                '" . zen_db_input($products->fields['products_url']) . "')");
            $products->MoveNext();
          }

// create additional meta_tags_products_description records
          $meta_tags_products = $db->Execute("select mt.products_id, mt.metatags_title, mt.metatags_keywords,
                                           mt.metatags_description
                                    from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION. " mt
                                    where mt.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$meta_tags_products->EOF) {
            $db->Execute("insert into " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . "
                        (products_id, language_id, metatags_title, metatags_keywords, metatags_description)
                        values ('" . (int)$meta_tags_products->fields['products_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($meta_tags_products->fields['metatags_title']) . "',
                                '" . zen_db_input($meta_tags_products->fields['metatags_keywords']) . "',
                                '" . zen_db_input($meta_tags_products->fields['metatags_description']) . "')");
            $meta_tags_products->MoveNext();
          }

// create additional meta_tags_categories_description records
          $meta_tags_categories = $db->Execute("select mt.categories_id, mt.metatags_title, mt.metatags_keywords,
                                           mt.metatags_description
                                    from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION. " mt
                                    where mt.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$meta_tags_categories->EOF) {
            $db->Execute("insert into " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . "
                        (categories_id, language_id, metatags_title, metatags_keywords, metatags_description)
                        values ('" . (int)$meta_tags_categories->fields['categories_id'] . "',
                                '" . (int)$insert_id . "',
                                '" . zen_db_input($meta_tags_categories->fields['metatags_title']) . "',
                                '" . zen_db_input($meta_tags_categories->fields['metatags_keywords']) . "',
                                '" . zen_db_input($meta_tags_categories->fields['metatags_description']) . "')");
            $meta_tags_categories->MoveNext();
          }

// create additional products_options records
          $products_options = $db->Execute("select products_options_id, products_options_name,
                              products_options_sort_order, products_options_type, products_options_length, products_options_comment, products_options_size,
                              products_options_images_per_row, products_options_images_style
                                           from " . TABLE_PRODUCTS_OPTIONS . "
                                           where language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$products_options->EOF) {
            $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS . "
                          (products_options_id, language_id, products_options_name,
                           products_options_sort_order, products_options_type, products_options_length, products_options_comment, products_options_size, products_options_images_per_row, products_options_images_style)
                          values ('" . (int)$products_options->fields['products_options_id'] . "',
                                  '" . (int)$insert_id . "',
                                  '" . zen_db_input($products_options->fields['products_options_name']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_sort_order']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_type']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_length']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_comment']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_size']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_images_per_row']) . "',
                                  '" . zen_db_input($products_options->fields['products_options_images_style']) . "')");

            $products_options->MoveNext();
          }

// create additional products_options_values records
          $products_options_values = $db->Execute("select products_options_values_id,
                                                   products_options_values_name, products_options_values_sort_order
                           from " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                           where language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$products_options_values->EOF) {
            $db->Execute("insert into " . TABLE_PRODUCTS_OPTIONS_VALUES . "
                        (products_options_values_id, language_id, products_options_values_name, products_options_values_sort_order)
                         values ('" . (int)$products_options_values->fields['products_options_values_id'] . "',
                                 '" . (int)$insert_id . "', '" . zen_db_input($products_options_values->fields['products_options_values_name']) . "', '" . zen_db_input($products_options_values->fields['products_options_values_sort_order']) . "')");

            $products_options_values->MoveNext();
          }

// create additional manufacturers_info records
          $manufacturers = $db->Execute("select m.manufacturers_id, mi.manufacturers_url
                                       from " . TABLE_MANUFACTURERS . " m
                           left join " . TABLE_MANUFACTURERS_INFO . " mi
                           on m.manufacturers_id = mi.manufacturers_id
                           where mi.languages_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$manufacturers->EOF) {
            $db->Execute("insert into " . TABLE_MANUFACTURERS_INFO . "
                         (manufacturers_id, languages_id, manufacturers_url)
                          values ('" . $manufacturers->fields['manufacturers_id'] . "', '" . (int)$insert_id . "',
                                  '" . zen_db_input($manufacturers->fields['manufacturers_url']) . "')");

            $manufacturers->MoveNext();
          }

// create additional orders_status records
          $orders_status = $db->Execute("select orders_status_id, orders_status_name
                                         from " . TABLE_ORDERS_STATUS . "
                                         where language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$orders_status->EOF) {
            $db->Execute("insert into " . TABLE_ORDERS_STATUS . "
                          (orders_status_id, language_id, orders_status_name)
                          values ('" . (int)$orders_status->fields['orders_status_id'] . "',
                                  '" . (int)$insert_id . "',
                                  '" . zen_db_input($orders_status->fields['orders_status_name']) . "')");
            $orders_status->MoveNext();
          }

          // create additional coupons_description records
          $coupons = $db->Execute("select c.coupon_id, cd.coupon_name, cd.coupon_description
                                    from " . TABLE_COUPONS . " c
                                    left join " . TABLE_COUPONS_DESCRIPTION . " cd
                                    on c.coupon_id = cd.coupon_id
                                    where cd.language_id = '" . (int)$_SESSION['languages_id'] . "'");

          while (!$coupons->EOF) {
            $db->Execute("insert into " . TABLE_COUPONS_DESCRIPTION . "
                          (coupon_id, language_id, coupon_name, coupon_description)
                           values ('" . (int)$coupons->fields['coupon_id'] . "',
                                   '" . (int)$insert_id . "',
                                   '" . zen_db_input($coupons->fields['coupon_name']) . "',
                                   '" . zen_db_input($coupons->fields['coupon_description']) . "')");
            $coupons->MoveNext();
          }

          zen_redirect(zen_href_link(FILENAME_LANGUAGES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'lID=' . $insert_id));
        }

        break;
      case 'save':
        //prepare/sanitize inputs
        $lID = zen_db_prepare_input($_GET['lID']);
        $name = zen_db_prepare_input($_POST['name']);
        $code = zen_db_prepare_input($_POST['code']);
        $image = zen_db_prepare_input($_POST['image']);
        $directory = zen_db_prepare_input($_POST['directory']);
        $sort_order = zen_db_prepare_input($_POST['sort_order']);

        // check if the spelling of the name for the default language has just been changed (thus meaning we need to change the spelling of DEFAULT_LANGUAGE to match it)
// get "code" for the language we just updated
        $result = $db->Execute("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
// compare "code" vs DEFAULT_LANGUAGE
        $changing_default_lang = (DEFAULT_LANGUAGE == $result->fields['code']) ? true : false;
// compare whether "code" matches $code (which was just submitted in the edit form
        $default_needs_an_update = (DEFAULT_LANGUAGE == $code) ? false : true;
// if we just edited the default language id's name, then we need to update the database with the new name for default
        $default_lang_change_flag = ($default_needs_an_update && $changing_default_lang) ? true : false;

        // save new language settings
        $db->Execute("update " . TABLE_LANGUAGES . "
                      set name = '" . zen_db_input($name) . "', code = '" . zen_db_input($code) . "',
                      image = '" . zen_db_input($image) . "', directory = '" . zen_db_input($directory) . "',
                      sort_order = '" . zen_db_input($sort_order) . "'
                      where languages_id = '" . (int)$lID . "'");

        // update default language setting
        if ((isset($_POST['default']) && $_POST['default'] == 'on') || $default_lang_change_flag == true) {
          $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = '" . zen_db_input(substr($code,0,2)) . "'
                        where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
        }
        $lID = zen_db_prepare_input($_POST['lID']);
        $lng = $db->Execute("select languages_id
                             from " . TABLE_LANGUAGES . "
                             where code = '" . DEFAULT_LANGUAGE . "'");

        if ($lng->fields['languages_id'] == $lID) {
          $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = ''
                        where configuration_key = 'DEFAULT_LANGUAGE'");
        }
        $db->Execute("delete from " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_COUPONS_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_META_TAGS_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$lID . "'");
        $db->Execute("delete from " . TABLE_METATAGS_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$lID . "'");

        // if we just deleted our currently-selected language, need to switch to default lang:
        $lng = $db->Execute("select languages_id from " . TABLE_LANGUAGES . " where code = '" . DEFAULT_LANGUAGE . "'");
        if ((int)$_SESSION['languages_id'] == (int)$_POST['lID'])  $_SESSION['languages_id'] = $lng->fields['languages_id'];

        zen_redirect(zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $lID = zen_db_prepare_input($_GET['lID']);
        $lng = $db->Execute("select code from " . TABLE_LANGUAGES . " where languages_id = '" . (int)$lID . "'");
        $remove_language = true;
        if ($lng->fields['code'] == DEFAULT_LANGUAGE) {
          $remove_language = false;
          $messageStack->add(ERROR_REMOVE_DEFAULT_LANGUAGE, 'error');
        }
        break;
    }
  }
require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_NAME; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_LANGUAGE_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $languages_query_raw = "select languages_id, name, code, image, directory, sort_order from " . TABLE_LANGUAGES . " order by sort_order";
  $languages_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $languages_query_raw, $languages_query_numrows);
  $languages = $db->Execute($languages_query_raw);
  while (!$languages->EOF) {
    if ((!isset($_GET['lID']) || (isset($_GET['lID']) && ($_GET['lID'] == $languages->fields['languages_id']))) && !isset($lInfo) && (substr($action, 0, 3) != 'new')) {
      $lInfo = new objectInfo($languages->fields);
    }
    if (isset($lInfo) && is_object($lInfo) && ($languages->fields['languages_id'] == $lInfo->languages_id) ) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages->fields['languages_id']) . '\'">' . "\n";
    }
    if (DEFAULT_LANGUAGE == $languages->fields['code']) {
      echo '                <td class="dataTableContent"><b>' . $languages->fields['name'] . ' (' . TEXT_DEFAULT . ')</b></td>' . "\n";
    } else {
      echo '                <td class="dataTableContent">' . $languages->fields['name'] . '</td>' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $languages->fields['code']; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($lInfo) && is_object($lInfo) && ($languages->fields['languages_id'] == $lInfo->languages_id)) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $languages->fields['languages_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $languages->MoveNext();
  }
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $languages_split->display_count($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_LANGUAGES); ?></td>
                    <td class="smallText" align="right"><?php echo $languages_split->display_links($languages_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td align="right" colspan="2"><?php echo '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=new') . '">' . zen_image_button('button_new_language.gif', IMAGE_NEW_LANGUAGE) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
    case 'new':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_LANGUAGE . '</b>');
      $contents = array('form' => zen_draw_form('languages', FILENAME_LANGUAGES, 'action=insert'));
      $contents[] = array('text' => TEXT_INFO_INSERT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . zen_draw_input_field('name'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . zen_draw_input_field('code', '', 'maxlength="2" size="4"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . zen_draw_input_field('image', 'icon.gif'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . zen_draw_input_field('directory'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . zen_draw_input_field('sort_order'));
      $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_LANGUAGE . '</b>');
      $contents = array('form' => zen_draw_form('languages', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . '<br>' . zen_draw_input_field('name', htmlspecialchars($lInfo->name, ENT_COMPAT, CHARSET, TRUE)));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_CODE . '<br>' . zen_draw_input_field('code', $lInfo->code, 'maxlength="2" size="4"'));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_IMAGE . '<br>' . zen_draw_input_field('image', $lInfo->image));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . zen_draw_input_field('directory', $lInfo->directory));
      $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . '<br>' . zen_draw_input_field('sort_order', $lInfo->sort_order));
      if (DEFAULT_LANGUAGE != $lInfo->code) $contents[] = array('text' => '<br>' . zen_draw_checkbox_field('default') . ' ' . TEXT_SET_DEFAULT);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_LANGUAGE . '</b>');
      $contents = array('form'=>zen_draw_form('delete', FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('lID', $lInfo->languages_id));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $lInfo->name . '</b>');
      $contents[] = array('text'=> (($remove_language) ? zen_image_submit('button_delete.gif', IMAGE_DELETE) : '') . ' <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $_GET['lID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>', 'align'=>'center');
      break;
    default:
      if (is_object($lInfo)) {
        $heading[] = array('text' => '<b>' . $lInfo->name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_LANGUAGES, 'page=' . $_GET['page'] . '&lID=' . $lInfo->languages_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_NAME . ' ' . $lInfo->name);
        $contents[] = array('text' => TEXT_INFO_LANGUAGE_CODE . ' ' . $lInfo->code);
        $contents[] = array('text' => '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $lInfo->directory . '/images/' . $lInfo->image, $lInfo->name));
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_DIRECTORY . '<br>' . DIR_WS_CATALOG_LANGUAGES . '<b>' . $lInfo->directory . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_LANGUAGE_SORT_ORDER . ' ' . $lInfo->sort_order);
      }
      break;
  }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
