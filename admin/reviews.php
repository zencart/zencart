<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: ZenExpert 2026 Jan 12 Modified in v2.2.0-alpha $
 */
require 'includes/application_top.php';

$action = $_GET['action'] ?? '';

// -----
// Note: Potential double/trailing ampersands are resolved via zen_href_link's processing.
//
$currentPage = (int)($_GET['page'] ?? 1);
$page_param = $currentPage > 1 ? 'page=' . $currentPage . '&' : '';

$status = ($_GET['status'] ?? '') !== '' ? (int)$_GET['status'] : 0;
$status_param = $status !== 0 ? 'status=' . $status . '&' : '';

// -----
// If the current action requires an rID (reviews_id) parameter, but
// that parameter isn't set, redirect back to the first page of the listing.
//
$rID = $_POST['rID'] ?? $_GET['rID'] ?? null;
if (in_array($action, ['edit', 'preview', 'setflag', 'update', 'delete', 'deleteconfirm']) && $rID === null) {
    zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param));
}

$status_list = [
    ['id' => '', 'text' => TEXT_ALL_STATUS],
    ['id' => 1, 'text' => TEXT_PENDING_APPROVAL],
    ['id' => 2, 'text' => TEXT_APPROVED],
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
                zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param));
            }
            break;

        case 'setflag':
            if (isset($_POST['flag'], $_GET['rID']) && ($_POST['flag'] == 1 || $_POST['flag'] == 0)) {
                zen_set_reviews_status($_GET['rID'], $_POST['flag']);
            }
            zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $_GET['rID']));
            break;

        case 'update':
            $reviews_id = (int)$_GET['rID'];
            if (!isset($_POST['reviews_rating'], $_POST['reviews_text'], $_POST['reviews_title']) || $reviews_id < 1) {
                zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param));
            }

            $reviews_rating = (int)$_POST['reviews_rating'];
            $reviews_text = zen_db_prepare_input($_POST['reviews_text']);
            $reviews_title = zen_db_prepare_input($_POST['reviews_title']);

            $db->Execute(
                "UPDATE " . TABLE_REVIEWS . "
                    SET reviews_rating = " . (int)$reviews_rating . ",
                        last_modified = now()
                  WHERE reviews_id = " . (int)$reviews_id . "
                  LIMIT 1"
            );

            $db->Execute(
                "UPDATE " . TABLE_REVIEWS_DESCRIPTION . "
                    SET reviews_text = '" . zen_db_input($reviews_text) . "',
                        reviews_title = '" . zen_db_input($reviews_title) . "'
                  WHERE reviews_id = " . (int)$reviews_id
            );

            zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $reviews_id));
            break;

        case 'deleteconfirm':
            $reviews_id = (int)$_POST['rID'];

            $db->Execute(
                "DELETE FROM " . TABLE_REVIEWS . "
                  WHERE reviews_id = " . (int)$reviews_id . "
                  LIMIT 1"
            );

            $db->Execute(
                "DELETE FROM " . TABLE_REVIEWS_DESCRIPTION . "
                  WHERE reviews_id = " . (int)$reviews_id
            );

            zen_redirect(zen_href_link(FILENAME_REVIEWS, $page_param . $status_param));
            break;

        default:
            break;
    }
}
?>
<!doctype html>
<html <?= HTML_PARAMS ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid">
        <h1><?= HEADING_TITLE ?></h1>
        <!-- body_text //-->
<?php
if ($action === 'edit' || $action === 'preview') {
    if (!empty($_POST)) {
        $rInfo = new objectInfo($_POST);
    } else {
        $rID = (int)$_GET['rID'];

        $reviews = $db->Execute(
            "SELECT r.reviews_id, r.products_id, r.customers_name, r.date_added, r.last_modified, r.reviews_read, rd.reviews_text, rd.reviews_title, r.reviews_rating
               FROM " . TABLE_REVIEWS . " r
                    LEFT OUTER JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd
                        ON r.reviews_id = rd.reviews_id
              WHERE r.reviews_id = " . (int)$rID
        );

        $products = $db->Execute(
            "SELECT p.products_image, pd.products_name
               FROM " . TABLE_PRODUCTS . " p
                    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                        ON pd.products_id = p.products_id
                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
              WHERE p.products_id = " . (int)$reviews->fields['products_id']
        );

        $rInfo_array = array_merge($reviews->fields, $products->fields);
        $rInfo = new objectInfo($rInfo_array);
    }
}

if ($action === 'edit') {
?>
        <div class="row">
            <?= zen_draw_form('update', FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $_GET['rID'] . '&action=update', 'post', 'class="form-horizontal"') ?>
            <div class="form-group">
                <?= zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) ?>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?= ENTRY_PRODUCT ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= $rInfo->products_name ?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?= ENTRY_FROM ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= $rInfo->customers_name ?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?= ENTRY_DATE ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= zen_date_short($rInfo->date_added) ?></span>
                </div>
            </div>
            <div class="form-group">
                <?= zen_draw_label(ENTRY_REVIEW_TITLE, 'reviews_title', 'class="control-label col-sm-3"') ?>
                <div class="col-sm-9 col-md-6">
                    <?= zen_draw_input_field('reviews_title', (!empty($rInfo->reviews_title) ? htmlspecialchars(stripslashes($rInfo->reviews_title), ENT_COMPAT, CHARSET, true) : ''), zen_set_field_length(TABLE_REVIEWS_DESCRIPTION, 'reviews_title', '128') . 'class="form-control" id="reviews_title"') ?>
                </div>
            </div>
            <div class="form-group">
                <?= zen_draw_label(ENTRY_REVIEW, 'reviews_text', 'class="control-label col-sm-3"') ?>
                <div class="col-sm-9 col-md-6">
                    <?= zen_draw_textarea_field('reviews_text', 'soft', '70', '15', htmlspecialchars(stripslashes($rInfo->reviews_text ?? ''), ENT_COMPAT, CHARSET, true), 'class="noEditor form-control" id="reviews_text"') ?>
                    <span class="help-block"><?= ENTRY_REVIEW_TEXT ?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-3">
                    <p class="control-label"><?= ENTRY_RATING ?></p>
                </div>
                <div class="col-sm-9 col-md-6">
                    <?= TEXT_BAD . '&nbsp;' ?>
<?php
        for ($i = 1; $i <= 5; $i++) {
            echo zen_draw_radio_field('reviews_rating', $i, '', $rInfo->reviews_rating, 'id="star' . $i . '"') . '&nbsp;';
        }
?>
                    <?= TEXT_GOOD ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12 text-right">
                    <?= zen_draw_hidden_field('reviews_id', $rInfo->reviews_id) ?>
                    <button type="submit" class="btn btn-primary"><?= IMAGE_UPDATE ?></button>&nbsp;
                    <a href="<?= zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $_GET['rID']) ?>" class="btn btn-default" role="button"><?= IMAGE_CANCEL ?></a>
                </div>
            </div>
            <?= '</form>' ?>
        </div>
<?php
} elseif ($action === 'preview') {
?>
        <div class="row">
            <div class="form-horizontal">
                <div class="form-group">
                    <?= zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) ?>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_PRODUCT ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= $rInfo->products_name ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_FROM ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= $rInfo->customers_name ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_DATE ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= zen_date_short($rInfo->date_added) ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_REVIEW_TITLE ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= zen_output_string_protected($rInfo->reviews_title) ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_REVIEW ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none"><?= nl2br(zen_output_string_protected(zen_trunc_string($rInfo->reviews_text, 15))) ?></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-3">
                        <p class="control-label"><?= ENTRY_RATING ?></p>
                    </div>
                    <div class="col-sm-9 col-md-6">
                        <span class="form-control" style="border:none; -webkit-box-shadow: none" title="<?= sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating) ?>">
                            <?= str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$rInfo->reviews_rating) ?>
                            &nbsp;<small>[<?= sprintf(TEXT_OF_5_STARS, $rInfo->reviews_rating) ?>]</small>
                        </span>
                    </div>
                </div>
<?php
    if (isset($_GET['origin'])) {
        $back_url = $_GET['origin'];
        $back_url_params = '';
    } else {
        $back_url = FILENAME_REVIEWS;
        $back_url_params = $page_param . $status_param . 'rID=' . $rInfo->reviews_id;
    }
?>
                <div class="form-group">
                    <div class="col-sm-12 text-right">
                        <a href="<?= zen_href_link($back_url, $back_url_params) ?>" class="btn btn-default" role="button"><?= IMAGE_BACK ?></a>
                        <a href="<?= zen_href_link(FILENAME_REVIEWS, $page_param . $status_param. 'rID=' . $rInfo->reviews_id . '&action=edit') ?>" class="btn btn-primary" role="button">
                            <?= TEXT_EDIT_REVIEW ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
<?php
} else {
    $status_filter = $_GET['status'] ?? '';
?>
        <div class="row">
            <div class="col-sm-offset-4 col-sm-4">
                <?= zen_draw_form('status_form', FILENAME_REVIEWS, '', 'get', 'class="form-horizontal"', true) ?>
                <div class="form-group">
                    <?= zen_draw_label(HEADING_TITLE_STATUS, 'status', 'class="control-label col-sm-3"') ?>
                    <div class="col-sm-9">
                        <?= zen_draw_pull_down_menu('status', $status_list, $status_filter, 'onchange="this.form.submit();" class="form-control" id="status"') ?>
                    </div>
                </div>
                <?= zen_hide_session_id() ?>
                <?= '</form>' ?>
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
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_MODEL ?></th>
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_PRODUCTS_NAME ?></th>
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_CUSTOMER_NAME ?></th>
<?php
    if (count($languages_array) > 1) {
?>
                        <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_LANGUAGE ?></th>
<?php
    }
?>
                        <th class="dataTableHeadingContent"><?= TABLE_HEADING_RATING ?></th>
                        <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_DATE_ADDED ?></th>
                        <th class="dataTableHeadingContent text-center"><?= TABLE_HEADING_STATUS ?></th>
                        <th class="dataTableHeadingContent text-right"><?= TABLE_HEADING_ACTION ?></th>
                    </tr>
                </thead>
                <tbody>
<?php
// create search filter
    $search = '';
    if (!empty($_GET['search'])) {
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

    if ((int)$status_filter > 0) {
        $search .= " AND r.status = " . ((int)$status_filter - 1);
    }

    $order_by = " ORDER BY r.status, r.date_added DESC";

    $reviews_query_raw =
        "SELECT r.reviews_id, r.products_id, r.customers_name, r.reviews_rating, r.date_added, r.status, r.last_modified, r.reviews_read,
                rd.languages_id, rd.reviews_text, rd.reviews_title,
                pd.products_name, p.products_model, p.products_image,
                LENGTH(rd.reviews_text) AS reviews_text_size
           FROM " . TABLE_REVIEWS . " r
                LEFT JOIN " . TABLE_REVIEWS_DESCRIPTION . " rd
                    ON rd.reviews_id = r.reviews_id
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd
                    ON pd.products_id = r.products_id
                   AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                LEFT JOIN " . TABLE_PRODUCTS . " p
                    ON p.products_id = r.products_id
          WHERE r.products_id = p.products_id " .
                $search .
          $order_by;

    // reset page when page is unknown
    if ($currentPage === 1 && !empty($_GET['rID'])) {
        $check_page = $db->Execute($reviews_query_raw);
        if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS) {
            $check_count = 0;
            foreach ($check_page as $item) {
                $check_count++;
                if ((int)$item['reviews_id'] === (int)$_GET['rID']) {
                    break;
                }
            }
            $currentPage = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS) != 0 ? .5 : 0)), 0);
            $page_param = $currentPage > 1 ? 'page=' . (int)$currentPage . '&' : '';
        }
    }

    $reviews_split = new splitPageResults($currentPage, MAX_DISPLAY_SEARCH_RESULTS, $reviews_query_raw, $reviews_query_numrows);
    $reviews = $db->Execute($reviews_query_raw);
    foreach ($reviews as $review) {
        if ((!isset($_GET['rID']) || (int)$_GET['rID'] === (int)$review['reviews_id']) && !isset($rInfo)) {
            $rInfo = new objectInfo($review);
        }

        if (isset($rInfo) && is_object($rInfo) && (int)$review['reviews_id'] === (int)$rInfo->reviews_id) {
?>
                    <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?= zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $rInfo->reviews_id . '&action=preview') ?>'">
<?php
        } else {
?>
                    <tr class="dataTableRow" onclick="document.location.href = '<?= zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $review['reviews_id']) ?>'">
<?php
        }
?>
                        <td class="dataTableContent" style="white-space:nowrap"><?= $review['products_model'] ?></td>
                        <td class="dataTableContent">
                            <a href="<?= zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $review['reviews_id'] . '&action=preview') ?>" title="<?= ICON_PREVIEW ?>">
                                <i class="fa-solid fa-binoculars fa-lg txt-black"></i>
                            </a>&nbsp;<?= zen_get_products_name($review['products_id']) ?>
                        </td>
                        <td class="dataTableContent"><?= $review['customers_name'] ?></td>
<?php
        if (count($languages_array) > 1) {
?>
                        <td class="dataTableContent text-center"><?= zen_get_language_icon($review['languages_id']) ?></td>
<?php
        }
?>
                        <td class="dataTableContent"><?= str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$review['reviews_rating']) ?></td>
                        <td class="dataTableContent text-center"><?= zen_date_short($review['date_added']) ?></td>
                        <td  class="dataTableContent text-center">
                            <?= zen_draw_form('setflag_products' . $review['reviews_id'], FILENAME_REVIEWS, $page_param . 'action=setflag&rID=' . $review['reviews_id']) ?>
<?php
        if ($review['status'] === '1') {
?>
                            <button type="submit" class="btn btn-status">
                                <i class="fa-solid fa-square fa-lg txt-status-on" title="<?= IMAGE_ICON_STATUS_ON ?>"></i>
                            </button>
                            <?= zen_draw_hidden_field('flag', '0') ?>
<?php
        } else {
?>
                            <button type="submit" class="btn btn-status">
                                <i class="fa-solid fa-square fa-lg txt-status-off" title="<?= IMAGE_ICON_STATUS_OFF ?>"></i>
                            </button>
                            <?= zen_draw_hidden_field('flag', '1') ?>
<?php
        }
?>
                            <?= '</form>'; ?>
                        </td>
                        <td class="dataTableContent text-right">
<?php
        if (isset($rInfo) && is_object($rInfo) && ($review['reviews_id'] === $rInfo->reviews_id)) {
            echo zen_icon('caret-right', '', '2x', true);
        } else {
?>
                            <a href="<?= zen_href_link(FILENAME_REVIEWS, zen_get_all_get_params(['rID']) . 'rID=' . $review['reviews_id']) ?>" title="<?= IMAGE_ICON_INFO ?>" role="button">
                                <?= zen_icon('circle-info') ?>
                            </a>
<?php
        }
?>
                        </td>
                    </tr>
<?php
    }   //- END foreach loop on listing page
?>
                </tbody>
                </table>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
<?php
    $heading = [];
    $contents = [];

    switch ($action) {
        case 'delete':
            $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_REVIEW . '</h4>'];

            $contents = ['form' => zen_draw_form('reviews', FILENAME_REVIEWS, $page_param . $status_param . 'action=deleteconfirm') . zen_draw_hidden_field('rID', $rInfo->reviews_id)];
            $contents[] = ['text' => TEXT_INFO_DELETE_REVIEW_INTRO];
            $contents[] = ['text' => '<b>' . $rInfo->products_name . '</b>'];
            $contents[] = [
                'align' => 'text-center',
                'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'
            ];
            break;

        default:
            if (!isset($rInfo) || !is_object($rInfo)) {
                break;
            }
            $heading[] = ['text' => '<h4>' . $rInfo->products_name . '</h4>'];

            $contents[] = [
                'align' => 'text-center',
                'text' =>
                    '<a href="' . zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $rInfo->reviews_id . '&action=edit') . '" class="btn btn-primary" role="button">' . TEXT_EDIT_REVIEW . '</a> ' .
                    '<a href="' . zen_href_link(FILENAME_REVIEWS, $page_param . $status_param . 'rID=' . $rInfo->reviews_id . '&action=delete') . '" class="btn btn-warning" role="button">' . TEXT_DELETE_REVIEW . '</a> '
            ];
            $contents[] = [
                'align' => 'text-center',
                'text' =>
                    '<a rel="noopener" target="_blank" href="' . zen_catalog_href_link(FILENAME_PRODUCT_REVIEWS_INFO, 'products_id=' . $rInfo->products_id . '&reviews_id=' . $rInfo->reviews_id) . '" class="btn btn-default" role="button">' . TEXT_VIEW_REVIEW . '</a> ' .
                    '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . zen_get_products_category_id($rInfo->products_id) . '&pID=' . $rInfo->products_id . '&action=new_product') . '" class="btn btn-default" role="button">' . TEXT_EDIT_PRODUCT . '</a>'
            ];

            $contents[] = ['text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_added)];
            if (!empty($rInfo->last_modified)) {
                $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->last_modified)];
            }
            $contents[] = ['text' => zen_info_image($rInfo->products_image, $rInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)];
            $contents[] = ['text' => ENTRY_REVIEW_TITLE . '<br>' . zen_output_string_protected($rInfo->reviews_title)];
            $contents[] = ['text' => ENTRY_REVIEW . '<br>' . zen_output_string_protected($rInfo->reviews_text)];
            $contents[] = ['text' => TEXT_INFO_REVIEW_AUTHOR . ' ' . $rInfo->customers_name];
            $contents[] = ['text' => TEXT_INFO_REVIEW_RATING . ' ' . str_repeat(zen_icon('star-shadow', size: 'lg'), (int)$rInfo->reviews_rating)];
            $contents[] = ['text' => TEXT_INFO_REVIEW_READ . ' ' . $rInfo->reviews_read];
            $contents[] = ['text' => TEXT_INFO_REVIEW_SIZE . ' ' . $rInfo->reviews_text_size . ' bytes'];

            $average_rating = $db->Execute(
                "SELECT (AVG(reviews_rating) / 5 * 100) AS avg
                   FROM " . TABLE_REVIEWS . "
                  WHERE products_id = " . (int)$rInfo->products_id
            );
            $contents[] = ['text' => TEXT_INFO_PRODUCTS_AVERAGE_RATING . ' ' . number_format((float)$average_rating->fields['avg'], 2) . '%'];
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
                    <td>
                        <?= $reviews_split->display_count($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $currentPage, TEXT_DISPLAY_NUMBER_OF_REVIEWS) ?>
                    </td>
                    <td class="text-right">
                        <?= $reviews_split->display_links($reviews_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $currentPage, zen_get_all_get_params(['page', 'rID'])) ?>
                    </td>
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
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';
