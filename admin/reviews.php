<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Steve 2024 Aug 02 Modified in v2.1.0-alpha2 $
 */
require 'includes/application_top.php';

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$status_filter = (isset($_GET['status']) ? $_GET['status'] : '');
$currentPage = (isset($_GET['page']) && $_GET['page'] != '' ? (int)$_GET['page'] : 0);
$status = (isset($_GET['status']) && $_GET['status'] != '' ? (int)$_GET['status'] : 0);

$status_list = [
  [
    'id' => '',
    'text' => TEXT_ALL_STATUS
  ],
  [
    'id' => 1,
    'text' => TEXT_PENDING_APPROVAL
  ],
  [
    'id' => 2,
    'text' => TEXT_APPROVED
  ]
];

if (!isset($languages_array)) {
  $languages_array = zen_get_languages();
}

if (!empty($action)) {
  switch ($action) {
    case 'edit':
    // same as 'preview'
    case 'preview':
      if (empty($_GET['rID'])) {
        zen_redirect(zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status : '')));
      }
      break;
    case 'setflag':
      if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
        zen_set_reviews_status($_GET['rID'], $_POST['flag']);
      }
      zen_redirect(zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $_GET['rID'], 'NONSSL'));
      break;
    case 'update':
      $reviews_id = zen_db_prepare_input($_GET['rID']);
      $reviews_rating = (int)$_POST['reviews_rating'];
      $reviews_text = zen_db_prepare_input($_POST['reviews_text']);

      $db->Execute("UPDATE " . TABLE_REVIEWS . "
                    SET reviews_rating = " . (int)$reviews_rating . ",
                        last_modified = now()
                    WHERE reviews_id = " . (int)$reviews_id);

      $db->Execute("UPDATE " . TABLE_REVIEWS_DESCRIPTION . "
                    SET reviews_text = '" . zen_db_input($reviews_text) . "'
                    WHERE reviews_id = " . (int)$reviews_id);

      zen_redirect(zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $_GET['rID']));
      break;
    case 'deleteconfirm':
      $reviews_id = zen_db_prepare_input($_POST['rID']);

      $db->Execute("DELETE FROM " . TABLE_REVIEWS . "
                    WHERE reviews_id = " . (int)$reviews_id);

      $db->Execute("DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . "
                    WHERE reviews_id = " . (int)$reviews_id);

      zen_redirect(zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status : '')));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?></h1>
      <!-- body_text //-->
      <?php
      if ($action == 'edit' || $action == 'preview') {
        if (!empty($_POST)) {
          $rInfo = new objectInfo($_POST);
        } else {
          $rID = (int)$_GET['rID'];

          $reviews = $db->Execute("SELECT r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating
                                   FROM " . TABLE_REVIEWS . " r,
                                        " . TABLE_REVIEWS_DESCRIPTION . " rd
                                   WHERE r.reviews_id = " . (int)$rID . "
                                   AND r.reviews_id = rd.reviews_id");

          $products = $db->Execute("SELECT p.products_image, pd.products_name
                                    FROM " . TABLE_PRODUCTS . " p
                                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = p.products_id
                                      AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                    WHERE p.products_id = " . (int)$reviews->fields['products_id']);

          $rInfo_array = array_merge($reviews->fields, $products->fields);
          $rInfo = new objectInfo($rInfo_array);
        }
      }
      if ($action == 'edit') {
        ?>
        <div class="row">
          <?php echo zen_draw_form('update', FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $_GET['rID'] . '&action=update', 'post', 'class="form-horizontal"'); ?>
          <div class="form-group">
            <?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?php echo ENTRY_PRODUCT; ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo $rInfo->products_name; ?></span>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?php echo ENTRY_FROM; ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo $rInfo->customers_name; ?></span>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?php echo ENTRY_DATE; ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo zen_date_short($rInfo->date_added); ?></span>
            </div>
          </div>
          <div class="form-group">
            <?php echo zen_draw_label(ENTRY_REVIEW, 'reviews_text', 'class="control-label col-sm-3"'); ?>
            <div class="col-sm-9 col-md-6">
              <?php echo zen_draw_textarea_field('reviews_text', 'soft', '70', '15', htmlspecialchars(stripslashes($rInfo->reviews_text), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor form-control" id="reviews_text"'); ?>
              <span class="help-block"><?php echo ENTRY_REVIEW_TEXT; ?></span>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-3">
              <p class="control-label"><?php echo ENTRY_RATING; ?></p>
            </div>
            <div class="col-sm-9 col-md-6">
              <?php echo TEXT_BAD; ?>
              <?php
              for ($i = 1; $i <= 5; $i++) {
                echo zen_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating, 'id="star' . $i . '"') . '&nbsp;';
              }
              ?>
              <?php echo TEXT_GOOD; ?>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-12 text-right">
              <?php echo zen_draw_hidden_field('reviews_id', $rInfo->reviews_id); ?>
              <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>&nbsp;<a href="<?php echo zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $_GET['rID']); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
            </div>
          </div>
          <?php echo '</form>'; ?>
        </div>
      <?php } elseif ($action == 'preview') { ?>
        <div class="row">
          <div class="form-horizontal">
            <div class="form-group">
              <?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?>
            </div>
            <div class="form-group">
              <div class="col-sm-3">
                <p class="control-label"><?php echo ENTRY_PRODUCT; ?></p>
              </div>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo $rInfo->products_name; ?></span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-3">
                <p class="control-label"><?php echo ENTRY_FROM; ?></p>
              </div>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo $rInfo->customers_name; ?></span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-3">
                <p class="control-label"><?php echo ENTRY_DATE; ?></p>
              </div>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo zen_date_short($rInfo->date_added); ?></span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-3">
                <p class="control-label"><?php echo ENTRY_REVIEW; ?></p>
              </div>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo nl2br(zen_output_string_protected(zen_break_string($rInfo->reviews_text, 15))); ?></span>
              </div>
            </div>
            <div class="form-group">
              <div class="col-sm-3">
                <p class="control-label"><?php echo ENTRY_RATING; ?></p>
              </div>
              <div class="col-sm-9 col-md-6">
                <span class="form-control" style="border:none; -webkit-box-shadow: none" title="<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating) ?>">
                  <?php echo str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$rInfo->reviews_rating); ?>
                  &nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating); ?>]</small></span>
              </div>
            </div>
            <?php
            if (isset($_GET['origin'])) {
              $back_url = $_GET['origin'];
              $back_url_params = '';
            } else {
              $back_url = FILENAME_REVIEWS;
              $back_url_params = ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id;
            }
            ?>
            <div class="form-group">
              <div class="col-sm-12 text-right">
                <a href="<?php echo zen_href_link($back_url, $back_url_params, 'NONSSL'); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
                <a href="<?php echo zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit'); ?>" class="btn btn-primary" role="button"><?php echo TEXT_EDIT_REVIEW; ?></a>
              </div>
            </div>
          </div>
        </div>
      <?php } else { ?>
        <div class="row">
          <div class="col-sm-offset-4 col-sm-4">
            <?php echo zen_draw_form('status_form', FILENAME_REVIEWS, '', 'get', 'class="form-horizontal"', true); ?>
            <div class="form-group">
              <?php echo zen_draw_label(HEADING_TITLE_STATUS, 'status', 'class="control-label col-sm-3"'); ?>
              <div class="col-sm-9">
                <?php echo zen_draw_pull_down_menu('status', $status_list, $status_filter, 'onchange="this.form.submit();" class="form-control" id="status"'); ?>
              </div>
            </div>
            <?php echo zen_hide_session_id(); ?>
            <?php echo '</form>'; ?>
          </div>
          <div class="col-sm-4">
            <?php require DIR_WS_MODULES . 'search_box.php'; ?>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODEL; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_NAME; ?></th>
                  <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER_NAME; ?></th>
                  <?php if (count($languages_array) > 1) { ?>
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
                  $keyword_search_fields = [
                    'r.customers_name',
                    'rd.reviews_text',
                    'pd.products_name',
                    'pd.products_description',
                    'p.products_model',
                  ];
                  $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords));
                }

                if ($status_filter != '' && $status_filter > 0) {
                  $search .= " AND r.status = " . ((int)$status_filter - 1);
                }

                $order_by = " ORDER BY r.status, r.date_added DESC";

                $reviews_query_raw = "SELECT r.reviews_id, r.products_id, r.customers_name, r.reviews_rating, r.date_added, r.status, r.last_modified, r.reviews_read,
                                             rd.languages_id, rd.reviews_text,
                                             pd.products_name,
                                             p.products_model, p.products_image,
                                             length(rd.reviews_text) AS reviews_text_size,
                                             (AVG(r.reviews_rating) / 5 * 100) AS average_rating
                                      FROM " . TABLE_REVIEWS . " r
                                      LEFT JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd ON rd.reviews_id = r.reviews_id
                                      LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = r.products_id
                                        AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                      LEFT JOIN " . TABLE_PRODUCTS . " p ON p.products_id = r.products_id
                                      WHERE r.products_id = p.products_id
                                      " . $search . "
                                      GROUP BY r.reviews_id, rd.languages_id, r.products_id, r.customers_name, r.reviews_rating, r.date_added, r.status,
                                       r.last_modified, r.reviews_read, rd.reviews_text, pd.products_name, p.products_model, p.products_image
                                      " . $order_by;

                // reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['rID'])) {
                  $check_page = $db->Execute($reviews_query_raw);
                  $check_count = 0;
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

                $reviews_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
                $reviews = $db->Execute($reviews_query_raw);
                foreach ($reviews as $review) {
                  if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ((int)$_GET['rID'] === (int)$review['reviews_id']))) && !isset($rInfo)) {
                    $rInfo = new objectInfo($review);
                  }

                  if (isset($rInfo) && is_object($rInfo) && ((int)$review['reviews_id'] === (int)$rInfo->reviews_id)) {
                    ?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=preview'); ?>'">
                    <?php } else { ?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $review['reviews_id']) ?>'">
                    <?php } ?>
                    <td class="dataTableContent" style="white-space:nowrap"><?php echo $review['products_model']; ?></td>
                    <td class="dataTableContent"><a href="<?php echo zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $review['reviews_id'] . '&action=preview'); ?>" title="<?php echo ICON_PREVIEW; ?>"><i class="fa-solid fa-binoculars fa-lg txt-black"></i></a>&nbsp;<?php echo zen_get_products_name($review['products_id']); ?></td>
                    <td class="dataTableContent"><?php echo $review['customers_name']; ?></td>
                    <?php if (count($languages_array) > 1) { ?>
                      <td class="dataTableContent text-center"><?php echo zen_get_language_icon($review['languages_id']); ?></td>
                    <?php } ?>
                    <td class="dataTableContent"><?php echo str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$review['reviews_rating']) ?></td>
                    <td class="dataTableContent text-center"><?php echo zen_date_short($review['date_added']); ?></td>
                    <td  class="dataTableContent text-center">
                      <?php echo zen_draw_form('setflag_products', FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . 'action=setflag&rID=' . $review['reviews_id']); ?>
                      <?php if ($review['status'] === '1') { ?>
                        <button type="submit" class="btn btn-status">
                          <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo IMAGE_ICON_STATUS_ON; ?>"></i>
                        </button>
                        <?php echo zen_draw_hidden_field('flag', '0'); ?>
                      <?php } else { ?>
                        <button type="submit" class="btn btn-status">
                          <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>"></i>
                        </button>
                        <?php echo zen_draw_hidden_field('flag', '1'); ?>
                      <?php } ?>
                      <?php echo '</form>'; ?>
                    </td>
                    <td class="dataTableContent text-right">
                      <?php if (isset($rinfo) && is_object($rInfo) && ($review['reviews_id'] === $rInfo->reviews_id)) {
                        echo zen_icon('caret-right', '', '2x', true);
                      } else { ?>
                        <a href="<?php echo zen_href_link(FILENAME_REVIEWS, zen_get_all_get_params(['rID']) . 'rID=' . $review['reviews_id']); ?>" title="<?php echo IMAGE_ICON_INFO; ?>" role="button">
                          <?php echo zen_icon('circle-info') ?>
                        </a>
                      <?php } ?>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];

            switch ($action) {
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</h4>');

                $contents = array('form' => zen_draw_form('reviews', FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'action=deleteconfirm') . zen_draw_hidden_field('rID', $rInfo->reviews_id));
                $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
                $contents[] = array('text' => '<b>' . $rInfo->products_name . '</b>');
                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($rInfo) && is_object($rInfo)) {
                  $heading[] = array('text' => '<h4>' . $rInfo->products_name . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit') . '" class="btn btn-primary" role="button">' . TEXT_EDIT_REVIEW . '</a> ' . '<a href="' . zen_href_link(FILENAME_REVIEWS, ($currentPage != 0 ? 'page=' . $currentPage . '&' : '') . ($status != 0 ? 'status=' . $status . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=delete') . '" class="btn btn-warning" role="button">' . TEXT_DELETE_REVIEW . '</a> ');
                  $contents[] = array('align' => 'text-center', 'text' => '<a rel="noopener" target="_blank" href="' . zen_catalog_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $rInfo->products_id . '&reviews_id=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . TEXT_VIEW_REVIEW . '</a> ' . '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . zen_get_products_category_id($rInfo->products_id) . '&pID=' . $rInfo->products_id . '&action=new_product') . '" class="btn btn-default" role="button">' . TEXT_EDIT_PRODUCT . '</a>');

                  $contents[] = array('text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_added));
                  if (zen_not_null($rInfo->last_modified)) {
                    $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->last_modified));
                  }
                  $contents[] = array('text' => zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
                  $contents[] = array('text' => ENTRY_REVIEW . '<br>' . zen_output_string_protected($rInfo->reviews_text));
                  $contents[] = array('text' => TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name);
                  $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' .
                    str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$rInfo->reviews_rating));
                  $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
                  $contents[] = array('text' => TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
                  $contents[] = array('text' => TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format((float)$rInfo->average_rating, 2) . '%');
                }
                break;
            }

            if (!empty($heading) && !empty($contents)) {
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
      <?php } ?>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
