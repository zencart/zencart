<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: reviews.php 19330 2011-08-07 06:32:56Z drbyte $
 */

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  $status_filter = (isset($_GET['status']) ? $_GET['status'] : '');
  $status_list[] = array('id' => 1, 'text' => TEXT_PENDING_APPROVAL);
  $status_list[] = array('id' => 2, 'text' => TEXT_APPROVED);

  if (zen_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if (isset($_POST['flag']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0))
        {
          zen_set_reviews_status($_GET['rID'], $_POST['flag']);
        }
        zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'], 'NONSSL'));
        break;
      case 'update':
        $reviews_id = zen_db_prepare_input($_GET['rID']);
        $reviews_rating = zen_db_prepare_input($_POST['reviews_rating']);
        $reviews_text = zen_db_prepare_input($_POST['reviews_text']);

        $db->Execute("update " . TABLE_REVIEWS . "
                      set reviews_rating = '" . zen_db_input($reviews_rating) . "',
                      last_modified = now() where reviews_id = '" . (int)$reviews_id . "'");

        $db->Execute("update " . TABLE_REVIEWS_DESCRIPTION . "
                      set reviews_text = '" . zen_db_input($reviews_text) . "'
                      where reviews_id = '" . (int)$reviews_id . "'");

        zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID']));
        break;
      case 'deleteconfirm':
        // demo active test
        if (zen_admin_demo()) {
          $_GET['action']= '';
          $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
          zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] : '')));
        }
        $reviews_id = zen_db_prepare_input($_POST['rID']);

        $db->Execute("delete from " . TABLE_REVIEWS . "
                      where reviews_id = '" . (int)$reviews_id . "'");

        $db->Execute("delete from " . TABLE_REVIEWS_DESCRIPTION . "
                      where reviews_id = '" . (int)$reviews_id . "'");


        zen_redirect(zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] : '')));
        break;
    }
  }
require('includes/admin_html_head.php');
?>
<?php if ($editor_handler != '') include ($editor_handler); ?>
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
         <tr><?php echo zen_draw_form('search', FILENAME_REVIEWS, '', 'get'); ?>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td class="smallText" align="right">
<?php
// show reset search
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    echo '<a href="' . zen_href_link(FILENAME_REVIEWS) . '">' . zen_image_button('button_reset.gif', IMAGE_RESET) . '</a>&nbsp;&nbsp;';
  }
  echo HEADING_TITLE_SEARCH_DETAIL . ' ' . zen_draw_input_field('search') . zen_hide_session_id();
  if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    echo '<br/ >' . TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords;
  }
?>
            </td>
          </form></tr>
              <tr><?php echo zen_draw_form('status', FILENAME_REVIEWS, '', 'get', '', true); ?>
                <td class="smallText" colspan="3" align="right">
                  <?php
                    echo HEADING_TITLE_STATUS . ' ' . zen_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_STATUS)), $status_list), $status_filter, 'onChange="this.form.submit();"');
                    echo zen_hide_session_id();
                 ?>
                </td>
              </form></tr>
        </table></td>
      </tr>
<?php
  if ($action == 'edit') {
    $rID = zen_db_prepare_input($_GET['rID']);

    $reviews = $db->Execute("select r.reviews_id, r.products_id, r.customers_name, r.date_added,
                                    r.last_modified, r.reviews_read, rd.reviews_text, r.reviews_rating
                             from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                             where r.reviews_id = '" . (int)$rID . "' and r.reviews_id = rd.reviews_id");

    $products = $db->Execute("select products_image
                              from " . TABLE_PRODUCTS . "
                              where products_id = '" . (int)$reviews->fields['products_id'] . "'");

    $products_name = $db->Execute("select products_name
                                   from " . TABLE_PRODUCTS_DESCRIPTION . "
                                   where products_id = '" . (int)$reviews->fields['products_id'] . "'
                                   and language_id = '" . (int)$_SESSION['languages_id'] . "'");

    $rInfo_array = array_merge($reviews->fields, $products->fields, $products_name->fields);
    $rInfo = new objectInfo($rInfo_array);
?>
      <tr><?php echo zen_draw_form('review', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'] . '&action=preview'); ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><b><?php echo ENTRY_PRODUCT; ?></b> <?php echo $rInfo->products_name; ?><br><b><?php echo ENTRY_FROM; ?></b> <?php echo $rInfo->customers_name; ?><br><br><b><?php echo ENTRY_DATE; ?></b> <?php echo zen_date_short($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><b><?php echo ENTRY_REVIEW; ?></b><br><br><?php echo zen_draw_textarea_field('reviews_text', 'soft', '70', '15', htmlspecialchars(stripslashes($rInfo->reviews_text), ENT_COMPAT, CHARSET, TRUE), 'class="noEditor"'); ?></td>
          </tr>
          <tr>
            <td class="smallText" align="right"><?php echo ENTRY_REVIEW_TEXT; ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo TEXT_BAD; ?>&nbsp;<?php for ($i=1; $i<=5; $i++) echo zen_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating) . '&nbsp;'; echo TEXT_GOOD; ?></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td align="right" class="main"><?php echo zen_draw_hidden_field('reviews_id', $rInfo->reviews_id) . zen_draw_hidden_field('products_id', $rInfo->products_id) . zen_draw_hidden_field('customers_name', $rInfo->customers_name) . zen_draw_hidden_field('products_name', $rInfo->products_name) . zen_draw_hidden_field('products_image', $rInfo->products_image) . zen_draw_hidden_field('date_added', $rInfo->date_added) . zen_image_submit('button_preview.gif', IMAGE_PREVIEW) . ' <a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID']) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </form></tr>
<?php
  } elseif ($action == 'preview') {
    if (zen_not_null($_POST)) {
      $rInfo = new objectInfo($_POST);
    } else {
      $rID = zen_db_prepare_input($_GET['rID']);

      $reviews = $db->Execute("select r.reviews_id, r.products_id, r.customers_name, r.date_added,
                                      r.last_modified, r.reviews_read, rd.reviews_text,
                                      r.reviews_rating
                               from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                               where r.reviews_id = '" . (int)$rID . "'
                               and r.reviews_id = rd.reviews_id");

      $products = $db->Execute("select products_image
                                from " . TABLE_PRODUCTS . "
                                where products_id = '" . (int)$reviews->fields['products_id'] . "'");

      $products_name = $db->Execute("select products_name
                                     from " . TABLE_PRODUCTS_DESCRIPTION . "
                                     where products_id = '" . (int)$reviews->fields['products_id'] . "'
                                     and language_id = '" . (int)$_SESSION['languages_id'] . "'");

      $rInfo_array = array_merge($reviews->fields, $products->fields, $products_name->fields);
      $rInfo = new objectInfo($rInfo_array);
    }
?>
      <tr><?php echo zen_draw_form('update', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $_GET['rID'] . '&action=update', 'post', 'enctype="multipart/form-data"'); ?>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="main" valign="top"><b><?php echo ENTRY_PRODUCT; ?></b> <?php echo $rInfo->products_name; ?><br><b><?php echo ENTRY_FROM; ?></b> <?php echo $rInfo->customers_name; ?><br><br><b><?php echo ENTRY_DATE; ?></b> <?php echo zen_date_short($rInfo->date_added); ?></td>
            <td class="main" align="right" valign="top"><?php echo zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT); ?></td>
          </tr>
        </table>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top" class="main"><b><?php echo ENTRY_REVIEW; ?></b><br><br><?php echo nl2br(zen_db_output(zen_break_string($rInfo->reviews_text, 15))); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><b><?php echo ENTRY_RATING; ?></b>&nbsp;<?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $rInfo->reviews_rating . '.gif', sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating)); ?>&nbsp;<small>[<?php echo sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating); ?>]</small></td>
      </tr>
      <tr>
        <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    if (zen_not_null($_POST)) {
/* Re-Post all POST'ed variables */
      reset($_POST);
      while(list($key, $value) = each($_POST)) echo zen_draw_hidden_field($key, $value);
?>
      <tr>
        <td align="right" class="smallText"><?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit') . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a> ' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </form></tr>
<?php
    } else {
      if (isset($_GET['origin'])) {
        $back_url = $_GET['origin'];
        $back_url_params = '';
      } else {
        $back_url = FILENAME_REVIEWS;
        $back_url_params = (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id;
      }
?>
      <tr>
        <td align="right"><?php echo '<a href="' . zen_href_link($back_url, $back_url_params, 'NONSSL') . '">' . zen_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
    }
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMER_NAME; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_RATING; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_ADDED; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php

// create search filter
    $search = '';
    if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
      $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
      $search = " and r.customers_name like '%" . $keywords . "%' or rd.reviews_text like '%" . $keywords . "%' or pd.products_name like '%" . $keywords . "%' or pd.products_description like '%" . $keywords . "%' or p.products_model like '%" . $keywords . "%'";
    }

    if ($status_filter !='' && $status_filter >0) $search .= " and r.status=" . ((int)$status_filter-1) . " ";

    $order_by = " order by pd.products_name";

    $reviews_query_raw = ("select r.*, rd.*, pd.*, p.*, c.* from (" . TABLE_REVIEWS . " r left join " . TABLE_REVIEWS_DESCRIPTION . " rd on r.reviews_id = rd.reviews_id left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on r.products_id = pd.products_id and pd.language_id ='" . (int)$_SESSION['languages_id'] . "' left join " . TABLE_PRODUCTS . " p on p.products_id= r.products_id) left join " . TABLE_CUSTOMERS . " c on r.customers_id = c.customers_id where r.products_id = p.products_id " . $search . $order_by);

// reset page when page is unknown
if (($_GET['page'] == '' or $_GET['page'] == '1') and $_GET['rID'] != '') {
  $check_page = $db->Execute($reviews_query_raw);
  $check_count=1;
  if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
    while (!$check_page->EOF) {
      if ($check_page->fields['reviews_id'] == $_GET['rID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/MAX_DISPLAY_SEARCH_RESULTS)+(fmod_round($check_count,MAX_DISPLAY_SEARCH_RESULTS) !=0 ? .5 : 0)),0);
  } else {
    $_GET['page'] = 1;
  }
}

//    $reviews_query_raw = "select reviews_id, products_id, date_added, last_modified, reviews_rating, status from " . TABLE_REVIEWS . " order by date_added DESC";
    $reviews_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
    $reviews = $db->Execute($reviews_query_raw);
    while (!$reviews->EOF) {
      if ((!isset($_GET['rID']) || (isset($_GET['rID']) && ($_GET['rID'] == $reviews->fields['reviews_id']))) && !isset($rInfo)) {
        $reviews_text = $db->Execute("select r.reviews_read, r.customers_name,
                                             length(rd.reviews_text) as reviews_text_size
                                      from " . TABLE_REVIEWS . " r, " . TABLE_REVIEWS_DESCRIPTION . " rd
                                      where r.reviews_id = '" . (int)$reviews->fields['reviews_id'] . "'
                                      and r.reviews_id = rd.reviews_id");

        $products_image = $db->Execute("select products_image
                                        from " . TABLE_PRODUCTS . "
                                        where products_id = '" . (int)$reviews->fields['products_id'] . "'");


        $products_name = $db->Execute("select products_name
                                       from " . TABLE_PRODUCTS_DESCRIPTION . "
                                       where products_id = '" . (int)$reviews->fields['products_id'] . "'
                                       and language_id = '" . (int)$_SESSION['languages_id'] . "'");

        $reviews_average = $db->Execute("select (avg(reviews_rating) / 5 * 100) as average_rating
                                         from " . TABLE_REVIEWS . "
                                         where products_id = '" . (int)$reviews->fields['products_id'] . "'");

        $review_info = array_merge($reviews_text->fields, $reviews_average->fields, $products_name->fields);
        $rInfo_array = array_merge($reviews->fields, $review_info, $products_image->fields);
        $rInfo = new objectInfo($rInfo_array);
      }

      if (isset($rInfo) && is_object($rInfo) && ($reviews->fields['reviews_id'] == $rInfo->reviews_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=preview') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $reviews->fields['reviews_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $reviews->fields['reviews_id'] . '&action=preview') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;' . zen_get_products_name($reviews->fields['products_id']); ?></td>
                <td class="dataTableContent"><?php echo $reviews->fields['customers_name'] . '<br />' . $reviews->fields['customers_firstname'] . ' ' . $reviews->fields['customers_lastname']; ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $reviews->fields['reviews_rating'] . '.gif'); ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_date_short($reviews->fields['date_added']); ?></td>
                <td  class="dataTableContent" align="center">
<?php
      if ($reviews->fields['status'] == '1') {
        echo zen_draw_form('setflag_products', FILENAME_REVIEWS, 'action=setflag&rID=' . $reviews->fields['reviews_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_green_on.gif" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" />
        <input type="hidden" name="flag" value="0" />
        </form>
<?php
      } else {
        echo zen_draw_form('setflag_products', FILENAME_REVIEWS, 'action=setflag&rID=' . $reviews->fields['reviews_id'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''));?>
        <input type="image" src="<?php echo DIR_WS_IMAGES ?>icon_red_on.gif" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" />
        <input type="hidden" name="flag" value="1" />
        </form>
<?php
      }
?>
                </td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($rInfo)) && ($reviews->fields['reviews_id'] == $rInfo->reviews_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $reviews->fields['reviews_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      $reviews->MoveNext();
    }
?>
              <tr>
                <td colspan="6"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_REVIEWS); ?></td>
                    <td class="smallText" align="right"><?php echo $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params( array( 'page', 'rID' ))); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();

    switch ($action) {
      case 'delete':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</b>');

        $contents = array('form' => zen_draw_form('reviews', FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'action=deleteconfirm') . zen_draw_hidden_field('rID', $rInfo->reviews_id));
        $contents[] = array('text' => TEXT_INFO_DELETE_REVIEW_INTRO);
        $contents[] = array('text' => '<br><b>' . $rInfo->products_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
      if (isset($rInfo) && is_object($rInfo)) {
        $heading[] = array('text' => '<b>' . $rInfo->products_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=edit') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . zen_href_link(FILENAME_REVIEWS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . (isset($_GET['status']) ? 'status=' . $_GET['status'] . '&' : '') . 'rID=' . $rInfo->reviews_id . '&action=delete') . '">' . zen_image_button('button_delete.gif', IMAGE_DELETE) . '</a> ' . ' <a href="' . zen_href_link(FILENAME_CATEGORIES, 'cPath=' . zen_get_products_category_id($rInfo->products_id) . '&pID=' . $rInfo->products_id) . '">' . zen_image_button('button_details.gif', IMAGE_DETAILS) . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_added));
        if (zen_not_null($rInfo->last_modified)) $contents[] = array('text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->last_modified));
        $contents[] = array('text' => '<br>' . zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT));
        $contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name . '<br />' .
        $rInfo->customers_firstname . ' ' . $rInfo->customers_lastname . '<br />' .
        '<a href="' . zen_href_link(FILENAME_CUSTOMERS, 'search=' . $rInfo->customers_email_address, 'SSL') . '" . >' . $rInfo->customers_email_address . '</a>');
        $contents[] = array('text' => TEXT_INFO_REVIEW_RATING . ' ' . zen_image(DIR_WS_TEMPLATE_IMAGES . 'stars_' . $rInfo->reviews_rating . '.gif'));
        $contents[] = array('text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read);
        $contents[] = array('text' => '<br>' . TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes');
        $contents[] = array('text' => '<br>' . TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format($rInfo->average_rating, 2) . '%');
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
<?php
  }
?>
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
