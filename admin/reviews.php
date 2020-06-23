<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 17 Modified in v1.5.7 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$status_filter = (isset($_GET['status']) ? $_GET['status'] : '');
$status_list = array();
$status_list[] = array('id' => 1, 'text' => TEXT_PENDING_APPROVAL);
$status_list[] = array('id' => 2, 'text' => TEXT_APPROVED);

if (!isset($languages_array)) {
    $languages_array = zen_get_languages();
}

if (zen_not_null($action)) {
  switch ($action) {
    case 'edit':
        // same as 'preview'
    case 'preview':
      if (empty($_GET['rID'])) {
          zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] : '')));
      }
      break;
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
        zen_set_reviews_status($_GET['rID'], $_POST['flag']);
      }
      zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'], 'NONSSL'));
      break;
    case 'update':
      $reviews_id = zen_db_prepare_input($_GET['rID']);
      $reviews_rating = zen_db_prepare_input($_POST['reviews_rating']);
      $reviews_text = zen_db_prepare_input($_POST['reviews_text']);

      $db->Execute("UPDATE " . TABLE_REVIEWS . "
                    SET reviews_rating = '" . zen_db_input($reviews_rating) . "',
                        last_modified = now()
                    WHERE reviews_id = " . (int)$reviews_id);

      $db->Execute("UPDATE " . TABLE_REVIEWS_DESCRIPTION . "
                    SET reviews_text = '" . zen_db_input($reviews_text) . "'
                    WHERE reviews_id = " . (int)$reviews_id);

      zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID']));
      break;
    case 'deleteconfirm':
      $reviews_id = zen_db_prepare_input($_POST['rID']);

      $db->Execute("DELETE FROM " . TABLE_REVIEWS . "
                    WHERE reviews_id = " . (int)$reviews_id);

      $db->Execute("DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . "
                    WHERE reviews_id = " . (int)$reviews_id);

      zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] : '')));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
      <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <?php if ($editor_handler != '') {
        include($editor_handler);
    } ?>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <div class="row text-right">
          <?php echo zen_draw_form('search', FILENAME_REVIEWS, '', 'get'); ?>
          <?php
// show reset search
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            echo '<a href="' . zen_href_link(FILENAME_REVIEWS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
          }
          echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
            echo '<br>' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
          }
          ?>
          <?php echo '</form>'; ?>
      </div>
      <div class="row text-right">
          <?php echo zen_draw_form('status', FILENAME_REVIEWS, '', 'get', '', true); ?>
          <?php
          echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_STATUS)), $status_list), $status_filter, 'onchange="this.form.submit();"');
          echo zen_hide_session_id();
          ?>
          <?php echo '</form>'; ?>
      </div>

      <!-- body_text //-->

      <?php
      if ($action == 'edit') {
        $rID = zen_db_prepare_input($_GET['rID']);

        $reviews = $db->Execute("SELECT r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating
                                 FROM " . TABLE_REVIEWS . " r,
                                      " . TABLE_REVIEWS_DESCRIPTION . " rd
                                 WHERE r.reviews_id = " . (int)$rID . "
                                 AND r.reviews_id = rd.reviews_id");

        $products = $db->Execute("SELECT products_image
                                  FROM " . TABLE_PRODUCTS . "
                                  WHERE products_id = " . (int)$reviews->fields['products_id']);

        $products_name = $db->Execute("SELECT products_name
                                       FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                       WHERE products_id = " . (int)$reviews->fields['products_id'] . "
                                       AND language_id = " . (int)$_SESSION['languages_id']);

        $rInfo_array = array_merge($reviews->fields, $products->fields, $products_name->fields);
        $rInfo = new objectInfo($rInfo_array);
        ?>
        <div class="row">
            <div><?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></div>
            <?php echo zen_draw_form('update', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'] . '&action=update'); ?>
          <div class="form-group">
            <strong><?php echo ENTRY_PRODUCT; ?></strong> <?php echo $rInfo->products_name; ?><br>
            <strong><?php echo ENTRY_FROM; ?></strong> <?php echo $rInfo->customers_name; ?><br>
            <strong><?php echo ENTRY_DATE; ?></strong> <?php echo zen_date_short($rInfo->date_added); ?><br>
              <?php echo zen_draw_label(ENTRY_REVIEW, 'reviews_text', 'class="control-label"'); ?>
              <?php echo zen_draw_textarea_field('reviews_text', 'soft', '70', '15', htmlspecialchars(stripslashes($rInfo->reviews_text), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control" id="reviews_text"'); ?>
          </div>
          <div class="row"><?php echo ENTRY_REVIEW_TEXT; ?></div>
        <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="row">
          <strong><?php echo ENTRY_RATING; ?></strong>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;<?php
          for ($i = 1; $i <= 5; $i++) {
              echo zen_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating) . '&nbsp;';
          }
            echo TEXT_GOOD;
          ?>
        </div>
        <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
        <div class="text-right">
            <?php echo zen_draw_hidden_field('reviews_id', $rInfo->reviews_id); ?>
            <?php echo zen_draw_hidden_field('products_id', $rInfo->products_id); ?>
            <?php echo zen_draw_hidden_field('customers_name', $rInfo->customers_name); ?>
            <?php echo zen_draw_hidden_field('products_name', $rInfo->products_name); ?>
            <?php echo zen_draw_hidden_field('products_image', $rInfo->products_image); ?>
            <?php echo zen_draw_hidden_field('date_added', $rInfo->date_added); ?>
            <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
            <a href="<?php echo zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        </div>
        <?php echo '</form>'; ?>
        </div>
        <?php
      } elseif ($action == 'preview') {
        if (zen_not_null($_POST)) {
          $rInfo = new objectInfo($_POST);
        } else {
          $rID = zen_db_prepare_input($_GET['rID']);

          $reviews = $db->Execute("SELECT r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating
                                   FROM " . TABLE_REVIEWS . " r,
                                        " . TABLE_REVIEWS_DESCRIPTION . " rd
                                   WHERE r.reviews_id = " . (int)$rID . "
                                   AND r.reviews_id = rd.reviews_id");

          $products = $db->Execute("SELECT products_image
                                    FROM " . TABLE_PRODUCTS . "
                                    WHERE products_id = " . (int)$reviews->fields['products_id']);

          $products_name = $db->Execute("SELECT products_name
                                         FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                         WHERE products_id = " . (int)$reviews->fields['products_id'] . "
                                         AND language_id = " . (int)$_SESSION['languages_id']);

          $rInfo_array = array_merge($reviews->fields, $products->fields, $products_name->fields);
          $rInfo = new objectInfo($rInfo_array);
        }
        ?>
        <div class="row">
            <div><?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></div>
            <?php echo zen_draw_form('update', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
            <div class="main">
                <strong><?php echo ENTRY_PRODUCT; ?></strong> <?php echo $rInfo->products_name; ?><br>
                <strong><?php echo ENTRY_FROM; ?></strong> <?php echo $rInfo->customers_name; ?><br>
                <strong><?php echo ENTRY_DATE; ?></strong> <?php echo zen_date_short($rInfo->date_added); ?>
            </div>
             <div class="main"><b><?php echo ENTRY_REVIEW; ?></b><br><br><?php echo nl2br(zen_db_output(zen_break_string($rInfo->reviews_text, 15))); ?></div>

            <?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?>

            <div class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $rInfo->reviews_rating . '.gif', sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating)); ?>&nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating); ?>]</small></div>

            <div><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>

          <?php
            if (isset($_GET['origin'])) {
              $back_url = $_GET['origin'];
              $back_url_params = '';
            } else {
              $back_url = FILENAME_REVIEWS;
              $back_url_params = (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id;
            }
            ?>
            <div class="text-right">
                <?php echo '<a href="' . zen_href_link($back_url, $back_url_params, 'NONSSL') . '" class="btn btn-default" role="button">' . IMAGE_BACK . '</a>'; ?>
                <?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit') . '" class="btn btn-primary" role="button">' . TEXT_EDIT_REVIEW . '</a> '; ?>
            </div>
        </div>
        <?php
      } else {
        ?>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCT; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER_NAME; ?></th>
             <?php if (count($languages_array) > 1) {?>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_LANGUAGE; ?></th>
             <?php } ?>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_RATING; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_DATE_ADDED; ?></th>
                  <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_STATUS; ?></th>
                  <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
// create search filter
                  $search = '';
                  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
                    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
                    $search = " AND (r.customers_name LIKE '%" . $keywords . "%'
                                OR rd.reviews_text LIKE '%" . $keywords . "%'
                                OR pd.products_name LIKE '%" . $keywords . "%'
                                OR pd.products_description LIKE '%" . $keywords . "%'
                                OR p.products_model LIKE '%" . $keywords . "%') ";
                  }

                  if ($status_filter != '' && $status_filter > 0) {
                    $search .= " AND r.status=" . ((int)$status_filter - 1);
                  }

	              $order_by = " ORDER BY r.status, r.date_added DESC";

                  $reviews_query_raw = "SELECT r.*, rd.*, pd.*, p.*
                                         FROM (" . TABLE_REVIEWS . " r
                                           LEFT JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON r.reviews_id = rd.reviews_id
                                           LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON r.products_id = pd.products_id
                                             AND pd.language_id ='" . (int)$_SESSION['languages_id'] . "'
                                           LEFT JOIN " . TABLE_PRODUCTS . " p ON p.products_id= r.products_id)
                                         WHERE r.products_id = p.products_id" . $search . $order_by;

// reset page when page is unknown
                  if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['rID'])) {
                    $check_page = $db->Execute($reviews_query_raw);
                    $check_count = 1;
                    if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
                      foreach ($check_page as $item) {
                        if ($item['reviews_id'] == $_GET['rID']) {
                          break;
                        }
                        $check_count++;
                      }
                      $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
                    } else {
                      $_GET['page'] = 1;
                    }
                  }

//    $reviews_query_raw = "select reviews_id, products_id, date_added, last_modified, reviews_rating, status from " . TABLE_REVIEWS . " order by date_added DESC";
                  $reviews_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
                  $reviews = $db->Execute($reviews_query_raw);
                  foreach ($reviews as $review) {
                    if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ($_GET['rID'] == $review['reviews_id']))) && !isset($rInfo)) {
                      $reviews_text = $db->Execute("SELECT r.reviews_read, r.customers_name, length(rd.reviews_text) AS reviews_text_size, rd.languages_id 
                                                    FROM " . TABLE_REVIEWS . " r,
                                                         " . TABLE_REVIEWS_DESCRIPTION . " rd
                                                    WHERE r.reviews_id = " . (int)$review['reviews_id'] . "
                                                    AND r.reviews_id = rd.reviews_id");

                      $products_image = $db->Execute("SELECT products_image
                                                      FROM " . TABLE_PRODUCTS . "
                                                      WHERE products_id = " . (int)$review['products_id']);

                      $products_model = $db->Execute("SELECT products_model
                                                      FROM " . TABLE_PRODUCTS . "
                                                      WHERE products_id = " . (int)$review['products_id']);

                      $products_name = $db->Execute("SELECT products_name
                                       FROM " . TABLE_PRODUCTS_DESCRIPTION . "
                                       WHERE products_id = " . (int)$review['products_id'] . "
                                       AND language_id = " . (int)$_SESSION['languages_id']);

                      $reviews_average = $db->Execute("SELECT (AVG(reviews_rating) / 5 * 100) AS average_rating
                                         FROM " . TABLE_REVIEWS . "
                                         WHERE products_id = " . (int)$review['products_id']);

                      $review_info = array_merge($reviews_text->fields, $reviews_average->fields, $products_name->fields, $products_model->fields);
                      $rInfo_array = array_merge($review, $review_info, $products_image->fields);
                      $rInfo = new objectInfo($rInfo_array);
                    }

                    if (isset($rInfo) && is_object($rInfo) && ($review['reviews_id'] == $rInfo->reviews_id)) { ?>
                      <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=preview'); ?>'">
                    <?php } else { ?>
                      <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $review['reviews_id']) ?>'">
                    <?php } ?>
                <td class="dataTableContent" style="white-space:nowrap"><?php echo $reviews->fields['products_model']; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $review['reviews_id'] . '&action=preview') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . zen_get_products_name($review['products_id']); ?></td>
                <td class="dataTableContent"><?php echo $review['customers_name']; ?></td>
    <?php if (count($languages_array) > 1) { ?>
                <td class="dataTableContent text-center"><?php echo zen_get_language_icon($review['languages_id']); ?></td>
     <?php } ?>
                <td class="dataTableContent"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $review['reviews_rating'] . '.gif'); ?></td>
                <td class="dataTableContent text-center"><?php echo zen_date_short($review['date_added']); ?></td>
                <td  class="dataTableContent text-center">
                    <?php
                    if ($review['status'] == '1') {
                      echo zen_draw_form('setflag_products', FILENAME_REVIEWS, 'action=setflag&rID=' . $review['reviews_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));
                      ?>
                    <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" alt="<?php echo IMAGE_ICON_STATUS_ON; ?>"/>
                    <?php echo zen_draw_hidden_field('flag', '0'); ?>
                    <?php echo '</form>'; ?>
                    <?php
                  } else {
                    echo zen_draw_form('setflag_products', FILENAME_REVIEWS, 'action=setflag&rID=' . $review['reviews_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));
                    ?>
                    <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" alt="<?php echo IMAGE_ICON_STATUS_OFF; ?>"/>
                    <?php echo zen_draw_hidden_field('flag', '1'); ?>
                    <?php echo '</form>'; ?>
                    <?php
                  }
                  ?>
                </td>
                <td class="dataTableContent text-right"><?php
                    if ((isset($rinfo) && is_object($rInfo)) && $review['reviews_id'] == $rInfo->reviews_id) {
                      echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif');
                    } else {
                      echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $review['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>';
                    }
                    ?>&nbsp;</td>
                </tr>
                <?php
              }
              ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
              <?php
              $heading = array();
              $contents = array();

              switch ($action) {
                case 'delete':
                  $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</h4>');

                  $contents = array('form' => zen_draw_form('reviews', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'action=deleteconfirm') . zen_draw_hidden_field('rID', $rInfo->reviews_id));
                  $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
                  $contents[] = array('text' => '<br><b>' . $rInfo->products_name . '</b>');
                  $contents[] = array('align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                  break;
                default:
                  if (isset($rInfo) && is_object($rInfo)) {
                    $heading[] = array('text' => '<h4>' . $rInfo->products_name . '</h4>');

                    $contents[] = array('align' => 'text-center', 'text' =>
                        '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit') . '" class="btn btn-primary" role="button">' . TEXT_EDIT_REVIEW . '</a> ' .
                        '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=delete') . '" class="btn btn-warning" role="button">' . TEXT_DELETE_REVIEW . '</a> ' .
                        '<a rel="noopener" target="_blank" href="' . zen_catalog_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $rInfo->products_id . '&reviews_id=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . TEXT_VIEW_REVIEW . '</a> ' .
                        '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . zen_get_products_category_id($rInfo->products_id) . '&pID=' . $rInfo->products_id . '&action=new_product') . '" class="btn btn-default" role="button">' . TEXT_EDIT_PRODUCT . '</a>');

                    $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_added));
                    if (zen_not_null($rInfo->last_modified)) {
                        $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->last_modified));
                    }
                    $contents[] = array('text' => '<br>' . zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
                    $contents[] = array('text' => '<br>' . ENTRY_REVIEW . '<br>' . $rInfo->reviews_text);
                    $contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name);
                    $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $rInfo->reviews_rating . '.gif'));
                    $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
                    $contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
                    $contents[] = array('text' => '<br>' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%');
                  }
                  break;
              }

              if ((zen_not_null($heading)) && (zen_not_null($contents))) {
                $box = new box();
                echo $box->infoBox($heading, $contents);
              }
              ?>
          </div>
        </div>
        <div class="row">
          <table class="table">
            <tr>
              <td><?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
              <td class="text-right"><?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'rID'))); ?></td>
            </tr>
          </table>
        </div>
        <?php
      }
      ?>
      <!-- body_text_eof //-->

    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
