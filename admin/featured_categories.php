<?php
/**
* @copyright Copyright 2003-2024 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: Jeff Rutt 2024 Aug 28 New in v2.1.0-alpha2 $
* based on featured (products)
*/
require 'includes/application_top.php';

$action = $_GET['action'] ?? '';
$currentPage = (int)($_GET['page'] ?? 0);
$page_search_parameters = ($currentPage !== 0 ? '&page=' . $currentPage : '') . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '');
$current_page = ($currentPage !== 0 ? 'page=' . $currentPage . '&' : '');
$search_parameters = (isset($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '');


if ($action !== '') {
    // -----
    // Set an indicator for init_special_funcs.php to perform auto-enable/expiration.
    //
    $_SESSION['expirationsNeedUpdate'] = true;

    switch ($action) {
        case 'setflag':
            if (isset($_POST['flag']) && ($_POST['flag'] === '1' || $_POST['flag'] === '0')) {
                zen_set_featured_category_status((int)$_POST['id'], (int)$_POST['flag']);
                zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, zen_get_all_get_params(['action', 'fID']) . 'fID=' . $_POST['id'], 'NONSSL'));
            }
            break;

        case 'insert':
            if (empty($_POST['categories_id'])) {
                $messageStack->add_session(ERROR_NOTHING_SELECTED, 'caution');
            } else {
                $categories_id = (int)$_POST['categories_id'];
                $error = false;
                $featured_date_available_raw = zen_db_prepare_input($_POST['featured_date_available']);
                if ($featured_date_available_raw === '') {
                    $featured_date_available = '0001-01-01';
                } else {
                    if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($featured_date_available_raw)) {
                        $local_fmt = zen_datepicker_format_fordate();
                        $dt = DateTime::createFromFormat($local_fmt, $featured_date_available_raw);
                        $featured_date_available_raw = 'null';
                        if (!empty($dt)) {
                            $featured_date_available_raw = $dt->format('Y-m-d');
                        }
                    }
                    if (zcDate::validateDate($featured_date_available_raw) === true) {
                        $featured_date_available = $featured_date_available_raw;
                    } else {
                        $error = true;
                        $messageStack->add(ERROR_INVALID_AVAILABLE_DATE, 'error');
                    }
                }
                $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
                if ($expires_date_raw === '') {
                    $expires_date = '0001-01-01';
                } else {
                    if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($expires_date_raw)) {
                        $local_fmt = zen_datepicker_format_fordate();
                        $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
                        $expires_date_raw = 'null';
                        if (!empty($dt)) {
                            $expires_date_raw = $dt->format('Y-m-d');
                        }
                    }
                    if (zcDate::validateDate($expires_date_raw) === true) {
                        $expires_date = $expires_date_raw;
                    } else {
                        $error = true;
                        $messageStack->add(ERROR_INVALID_EXPIRES_DATE, 'error');
                    }
                }
                if ($error === true) {
                    $action = 'new';
                    break;
                }

                $db->Execute(
                    "INSERT INTO " . TABLE_FEATURED_CATEGORIES . " (categories_id, featured_date_added, expires_date, status, featured_date_available)
                        VALUES (" . (int)$categories_id . ", now(), '" . zen_db_input($expires_date) . "', 1, '" . zen_db_input($featured_date_available) . "')"
                );

                $new_featured = $db->Execute(
                    "SELECT featured_categories_id
                    FROM " . TABLE_FEATURED_CATEGORIES . "
                    WHERE categories_id = " . (int)$categories_id
                );
            } // nothing selected
            if (isset($_GET['go_back']) && $_GET['go_back'] === 'ON') {
                zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . (isset($_GET['search']) ? '&search=' . $_GET['search'] . '&' : '') . (isset($new_featured) ? 'fID=' . $new_featured->fields['featured_categories_id'] : '')));
            }
            break;

        case 'update':
            $featured_categories_id = (int)$_POST['featured_categories_id'];
            $error = false;
            $featured_date_available_raw = zen_db_prepare_input($_POST['featured_date_available']);
            if ($featured_date_available_raw === '') {
                $featured_date_available = '0001-01-01';
            } else {
                if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($featured_date_available_raw)) {
                    $local_fmt = zen_datepicker_format_fordate();
                    $dt = DateTime::createFromFormat($local_fmt, $featured_date_available_raw);
                    $featured_date_available_raw = 'null';
                    if (!empty($dt)) {
                        $featured_date_available_raw = $dt->format('Y-m-d');
                    }
                }
                if (zcDate::validateDate($featured_date_available_raw) === true) {
                    $featured_date_available = $featured_date_available_raw;
                } else {
                    $error = true;
                    $messageStack->add(ERROR_INVALID_AVAILABLE_DATE, 'error');
                }
            }
            $expires_date_raw = zen_db_prepare_input($_POST['expires_date']);
            if ($expires_date_raw === '') {
                $expires_date = '0001-01-01';
            } else {
                if (DATE_FORMAT_DATE_PICKER !== 'yy-mm-dd' && !empty($expires_date_raw)) {
                    $local_fmt = zen_datepicker_format_fordate();
                    $dt = DateTime::createFromFormat($local_fmt, $expires_date_raw);
                    $expires_date_raw = 'null';
                    if (!empty($dt)) {
                        $expires_date_raw = $dt->format('Y-m-d');
                    }
                }
                if (zcDate::validateDate($expires_date_raw) === true) {
                    $expires_date = $expires_date_raw;
                } else {
                    $error = true;
                    $messageStack->add(ERROR_INVALID_EXPIRES_DATE, 'error');
                }
            }

            if ($error === true) {
                $action = 'edit';
                break;
            }

            $db->Execute(
                "UPDATE " . TABLE_FEATURED_CATEGORIES . "
                  SET featured_last_modified = now(),
                  expires_date = '" . zen_db_input($expires_date) . "',
                  featured_date_available = '" . zen_db_input($featured_date_available) . "'
                  WHERE featured_categories_id = " . $featured_categories_id
            );

            zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'fID=' . $featured_categories_id));
            break;

        case 'deleteconfirm':
            $featured_categories_id = (int)$_POST['fID'];
            $db->Execute(
                "DELETE FROM " . TABLE_FEATURED_CATEGORIES . "
                WHERE featured_categories_id = " . $featured_categories_id
            );
            zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . (isset($_GET['search']) ? 'search=' . $_GET['search'] : '')));
            break;

        case 'pre_add_confirmation':
            $skip_featured = false;
            // check for no CID entered
            if (empty($_POST['pre_add_categories_id'])) {
                $skip_featured = true;
                $messageStack->add_session(WARNING_FEATURED_PRE_ADD_CID_EMPTY, 'caution');
            } else {
                $sql = "SELECT categories_id
                        FROM " . TABLE_CATEGORIES . "
                        WHERE categories_id = " . (int)$_POST['pre_add_categories_id'];
                $check_featured = $db->Execute($sql);
                if ($check_featured->EOF) {// check for valid CID
                    $skip_featured = true;
                    $messageStack->add_session(sprintf(WARNING_FEATURED_PRE_ADD_CID_NO_EXIST, (int)$_POST['pre_add_categories_id']), 'caution');
                }
            }
            // check if Featured already exists
            if ($skip_featured === false) {
                $sql = "SELECT featured_categories_id
                        FROM " . TABLE_FEATURED_CATEGORIES . "
                        WHERE categories_id = " . (int)$_POST['pre_add_categories_id'];
                $check_featured = $db->Execute($sql);
                if ($check_featured->RecordCount() > 0) {
                    $skip_featured = true;
                    $messageStack->add_session(sprintf(WARNING_FEATURED_PRE_ADD_CID_DUPLICATE, (int)$_POST['pre_add_categories_id']), 'caution');
                }
            }
            if ($skip_featured === true) {
                zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . (!empty($check_featured->fields['featured_categories_id']) ? 'fID=' . (int)$check_featured->fields['featured_categories_id'] : '' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''))));
            } else { // category id is valid
                zen_redirect(zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . 'action=new' . '&preID=' . (int)$_POST['pre_add_categories_id']));
            }
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
        <div class="container-fluid">
            <!-- body //-->
            <h1><?php echo HEADING_TITLE; ?></h1>
            <!-- body_text //-->
            <?php
            if ($action === 'new' || $action === 'edit') {
                $form_action = 'insert';
                if ($action === 'edit' && isset($_GET['fID'])) {//update existing Featured
                    $form_action = 'update';

                    $category = $db->Execute("SELECT c.categories_id, cd.categories_name,
                                            fc.expires_date, fc.featured_date_available
                                            FROM " . TABLE_CATEGORIES . " c,
                                            " . TABLE_CATEGORIES_DESCRIPTION . " cd,
                                            " . TABLE_FEATURED_CATEGORIES . " fc
                                            WHERE c.categories_id = cd.categories_id
                                            AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                            AND c.categories_id = fc.categories_id
                                            AND fc.featured_categories_id = " . (int)$_GET['fID']);

                    $fInfo = new objectInfo($category->fields);
                } elseif ($action === 'new' && isset($_GET['preID'])) { //update existing Featured Category
                    $form_action = 'insert';

                    $category = $db->Execute("SELECT c.categories_id, cd.categories_name
                                            FROM " . TABLE_CATEGORIES . " c,
                                            " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                            WHERE c.categories_id = cd.categories_id
                                            AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                            AND c.categories_id = " . (int)$_GET['preID']);

                    $fInfo = new objectInfo($category->fields);
                } elseif (empty($_GET['preID'])) { // insert by category select dropdown
                    $fInfo = new objectInfo([]);

                    // create an array of featured categories, which will be excluded from the pull down menu of categories
                    // (when creating a new featured category)
                    $featured_array = [];
                    $featureds = $db->Execute("SELECT c.categories_id, cd.categories_name
                                                FROM " . TABLE_CATEGORIES . " c,
                                                " . TABLE_CATEGORIES_DESCRIPTION . " cd,
                                                " . TABLE_FEATURED_CATEGORIES . " fc
                                                WHERE fc.categories_id = c.categories_id
                                                AND cd.language_id = " . (int)$_SESSION['languages_id']);

                    foreach ($featureds as $featured) {
                        $featured_array[] = $featured['categories_id'];
                    }
                }

                echo TEXT_ADD_FEATURED_SELECT;

                if ($action === 'new' && !isset($_GET['preID'])) {
                    $form = addSearchKeywordForm(FILENAME_FEATURED_CATEGORIES, $action);
                    echo $form;
                }
                ?>
                <div class="row">
                    <?php echo zen_draw_form('new_featured', FILENAME_FEATURED_CATEGORIES, zen_get_all_get_params(['action', 'info', 'fID']) . 'action=' . $form_action . (!empty($_GET['go_back']) ? '&go_back=' . $_GET['go_back'] : ''), 'post', 'class="form-horizontal"'); ?>
                    <?php
                    if ($form_action === 'update') {
                        echo zen_draw_hidden_field('featured_categories_id', $_GET['fID']);
                    }
                    if (!empty($_GET['preID'])) { // new Special: insert by category ID
                        echo zen_draw_hidden_field('categories_id', $_GET['preID']);
                    }
                    ?>
                    <?php if (isset($fInfo->categories_name)) { // Featured is already defined/this is an update ?>
                        <div class="form-group">
                            <p class="col-sm-3 control-label"><?php echo TEXT_FEATURED_CATEGORY; ?></p>
                            <div class="col-sm-9 col-md-6">
                                <span class="form-control" style="border:none; -webkit-box-shadow: none"><?php echo 'ID#' . $fInfo->categories_id . ' - "' . zen_clean_html($fInfo->categories_name) . '"'; ?></span>
                            </div>
                        </div>
                        <?php
                    } elseif (!empty($_GET['preID'])) { // new Featured: insert by category ID
                        $preID = (int)$_GET['preID'];
                        ?>
                        <div class="form-group">
                            <p class="col-sm-3 control-label"><?php echo TEXT_FEATURED_CATEGORY; ?></p>
                            <div class="col-sm-9 col-md-6">
                                <span class="form-control" style="border:none; -webkit-box-shadow: none">
                                <?php echo 'ID#' . $preID . ': ' . zen_clean_html(zen_get_categories_name($preID)); ?></span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="form-group">
                            <?php echo zen_draw_label(TEXT_FEATURED_CATEGORY, 'categories_id', 'class="col-sm-3 control-label"'); ?>
                            <div class="col-sm-9 col-md-6">
                               <?php echo  zen_draw_pulldown_categories_having_products('categories_id', 'required size="15" class="form-control"', $featured_array, true,true,false,); ?>
                            </div>
                        </div>
                    <?php } ?>

                    <?php echo zen_draw_hidden_field('update_categories_id', $fInfo->categories_id); ?>
                    <div class="form-group">
                        <?php echo zen_draw_label(TEXT_FEATURED_AVAILABLE_DATE, 'featured_date_available', 'class="col-sm-3 control-label"'); ?>
                        <div class="col-sm-9 col-md-6">
                            <div class="date input-group" id="datepicker_featured_date_available">
                                <span class="input-group-addon datepicker_icon">
                                <?php echo zen_icon('calendar-days', size: 'lg') ?>
                                </span>
                                <?php echo zen_draw_input_field('featured_date_available',
                                (($fInfo->featured_date_available == '0001-01-01') ? '' : $fInfo->featured_date_available),
                                'class="form-control" id="featured_date_available"'); ?>
                            </div>
                            <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)
                            <span class="date-check-error"><?php echo ERROR_INVALID_ACTIVE_DATE; ?></span></span>
                        </div>
                    </div>
                <div class="form-group">
                    <?php echo zen_draw_label(TEXT_FEATURED_EXPIRES_DATE, 'expires_date', 'class="col-sm-3 control-label"'); ?>
                    <div class="col-sm-9 col-md-6">
                        <div class="date input-group" id="datepicker_expires_date">
                            <span class="input-group-addon datepicker_icon">
                            <?php echo zen_icon('calendar-days', size: 'lg') ?>
                            </span>
                            <?php echo zen_draw_input_field('expires_date',
                            (($fInfo->expires_date == '0001-01-01') ? '' : $fInfo->expires_date), 'class="form-control" id="expires_date"'); ?>
                        </div>
                        <span class="help-block errorText">(<?php echo zen_datepicker_format_full(); ?>)
                        <span class="date-check-error"><?php echo ERROR_INVALID_EXPIRES_DATE; ?></span></span>
                    </div>
                </div>
                <?php
                $cancel_link = zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . (!empty($_GET['search']) ? 'search=' . $_GET['search'] . '&' : '') . (!empty($_GET['fID']) ? 'fID=' . $_GET['fID'] : ''));
                ?>
                <?php require DIR_WS_INCLUDES . 'javascript/dateChecker.php'; ?>
                <div class="col-sm-12 text-right">
                    <button type="submit" class="btn btn-primary"><?php echo(($form_action === 'insert') ? IMAGE_INSERT : IMAGE_UPDATE); ?></button> <a class="btn btn-default" role="button" href="<?php echo $cancel_link; ?>"><?php echo IMAGE_CANCEL; ?></a>
                </div>
                <?php echo '</form>'; ?>
            </div>
            <?php } else { ?>
            <div class="row">
                <div class="col-sm-8">
                    <a href="<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . 'action=new'); ?>" class="btn btn-primary" role="button"><?php echo TEXT_ADD_FEATURED_SELECT; ?></a>
                    <a href="<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'action=pre_add'); ?>" class="btn btn-primary" role="button" title="<?php echo TEXT_INFO_PRE_ADD_INTRO; ?>"><?php echo TEXT_ADD_FEATURED_CID; ?></a>
                </div>
                <div class="col-sm-4">
                    <?php require DIR_WS_MODULES . 'search_box.php'; ?>
                </div>
            </div>
            <div class="row">
            <div><?php echo TEXT_STATUS_WARNING; ?></div>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
                <table class="table table-hover">
                    <thead>
                        <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent text-right"><?php echo 'ID'; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_CATEGORY_NAME; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ACTIVE_FROM; ?></th>
                        <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_EXPIRES_DATE; ?></th>
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
                                'cd.categories_name',
                                'cd.categories_description',
                                'c.categories_id',
                            ];
                            $search = zen_build_keyword_where_clause($keyword_search_fields, trim($keywords));
                        }

                        // order of display
                        $order_by = " ORDER BY cd.categories_name"; //set sort order of table listing
                        $featured_query_raw = "SELECT c.categories_id, c.parent_id, cd.categories_name,
                                                fc.featured_categories_id, fc.featured_date_added, fc.featured_last_modified,
                                                fc.expires_date, fc.date_status_change, fc.status, fc.featured_date_available
                                                FROM " . TABLE_CATEGORIES . " c,
                                                " . TABLE_FEATURED_CATEGORIES . " fc,
                                                " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                                WHERE c.categories_id = cd.categories_id
                                                AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                                AND c.categories_id = fc.categories_id
                                                " . $search . "
                                                " . $order_by;

                        // Split Page
                        // reset page when page is unknown
                        if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['fID'])) {
                            $check_page = $db->Execute($featured_query_raw);
                            if ($check_page->RecordCount() > MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) {
                                $check_count = 0;
                                foreach ($check_page as $item) {
                                    if ((int)$item['featured_categories_id'] === (int)$_GET['fID']) {
                                        break;
                                    }
                                $check_count++;
                                }
                                $_GET['page'] = round((($check_count / MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) + (fmod_round($check_count, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN) !== 0 ? .5 : 0)));
                                $page = $_GET['page'];
                            } else {
                                $_GET['page'] = 1;
                            }
                        }

                        // create split page control
                        $featured_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $featured_query_raw, $featured_query_numrows);
                        $featureds = $db->Execute($featured_query_raw);
                        foreach ($featureds as $featured) {
                            if ((!isset($_GET['fID']) || (int)$_GET['fID'] === (int)$featured['featured_categories_id']) && !isset($fInfo)) {
                                $categories = $db->Execute("SELECT categories_image
                                                            FROM " . TABLE_CATEGORIES . "
                                                            WHERE categories_id = " . (int)$featured['categories_id']);

                                $fInfo_array = array_merge($featured, $categories->fields);
                                $fInfo = new objectInfo($fInfo_array);
                            }

                            if (isset($fInfo) && is_object($fInfo) && ((int)$featured['featured_categories_id'] === (int)$fInfo->featured_categories_id)) {
                                ?>
                                <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page. $search_parameters . 'fID=' . $fInfo->featured_categories_id . '&action=edit'); ?>'">
                            <?php } else { ?>
                                <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'fID=' . $featured['featured_categories_id']); ?>'">
                                <?php
                            }
                            ?>
                            <td class="dataTableContent text-right"><?php echo $featured['categories_id']; ?></td>
                            <td class="dataTableContent"><?php echo zen_clean_html($featured['categories_name']); ?></td>
                            <td class="dataTableContent text-center"><?php echo(($featured['featured_date_available'] !== '0001-01-01' && $featured['featured_date_available'] !== '') ? zen_date_short($featured['featured_date_available']) : TEXT_NONE); ?></td>
                            <td class="dataTableContent text-center"><?php echo(($featured['expires_date'] !== '0001-01-01' && $featured['expires_date'] !== '') ? zen_date_short($featured['expires_date']) : TEXT_NONE); ?></td>
                            <td class="dataTableContent text-center">
                            <?php if (($featured['featured_date_available'] !== '0001-01-01' && $featured['featured_date_available'] !== '') || ($featured['expires_date'] !== '0001-01-01' && $featured['expires_date'] !== '')) { ?>
                                <button type="submit" class="btn btn-status" style="cursor: initial;">
                                <?php if ($featured['status'] === '1') { ?>
                                    <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo TEXT_FEATURED_ACTIVE; ?>: <?php echo TEXT_FEATURED_STATUS_BY_DATE; ?>"></i>
                                <?php } else { ?>
                                    <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo TEXT_FEATURED_INACTIVE; ?>: <?php echo TEXT_FEATURED_STATUS_BY_DATE; ?>"></i>
                                <?php } ?>
                                </button>
                            <?php } else { ?>
                                <?php echo zen_draw_form('setflag_categories_' . $featured['categories_id'], FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'action=setflag'); ?>
                                <?php if ($featured['status'] === '1') { ?>
                                    <button type="submit" class="btn btn-status">
                                    <i class="fa-solid fa-square fa-lg txt-status-on" title="<?php echo TEXT_FEATURED_ACTIVE; ?>"></i>
                                    </button>
                                    <?php echo zen_draw_hidden_field('flag', '0'); ?>
                                <?php } else { ?>
                                    <button type="submit" class="btn btn-status">
                                    <i class="fa-solid fa-square fa-lg txt-status-off" title="<?php echo TEXT_FEATURED_INACTIVE; ?>"></i>
                                    </button>
                                    <?php echo zen_draw_hidden_field('flag', '1'); ?>
                                <?php } ?>
                                <?php echo zen_draw_hidden_field('id', $featured['featured_categories_id']); ?>
                                <?php echo '</form>'; ?>
                            <?php } ?>
                            </td>
                            <td class="dataTableContent text-right actions">
                            <div class="btn-group">
                                <a href="<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'action=edit' . '&fID=' . $featured['featured_categories_id']); ?>" class="btn btn-sm btn-default btn-edit" role="button">
                                <?php echo zen_icon('pencil', ICON_EDIT) ?>
                                </a>
                                <a href="<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, $current_page . $search_parameters . 'action=delete' . '&fID=' . $featured['featured_categories_id']); ?>" class="btn btn-sm btn-default btn-delete" role="button">
                                <?php echo zen_icon('trash', ICON_DELETE) ?>
                                </a>
                            </div>
                            <?php if (isset($fInfo) && is_object($fInfo) && ($featured['featured_categories_id'] === $fInfo->featured_categories_id)) {
                                echo zen_icon('caret-right', '', '2x', true);
                            } else { ?>
                                <a href="<?php echo zen_href_link(FILENAME_FEATURED_CATEGORIES, zen_get_all_get_params(['fID']) . 'fID=' . $featured['featured_categories_id']); ?>" role="button">
                                <?php echo zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, true) ?>
                                </a>
                            <?php } ?>
                            </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <div class="row">
                <div class="col-sm-6"><?php echo $featured_split->display_count($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_FEATURED_CATEGORIES); ?></div>
                    <div class="col-sm-6 text-right"><?php echo $featured_split->display_links($featured_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_FEATURED_ADMIN, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(['page', 'fID'])); ?></div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
                    <?php
                    $heading = [];
                    $contents = [];

                    switch ($action) {
                        case 'delete':
                            $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_DELETE_FEATURED . '</h4>'];
                            $contents = ['form' => zen_draw_form('featured', FILENAME_FEATURED_CATEGORIES, 'action=deleteconfirm' . $page_search_parameters) . zen_draw_hidden_field('fID', $fInfo->featured_categories_id)];
                            $contents[] = ['text' => TEXT_INFO_DELETE_INTRO];
                            $contents[] = ['text' => '<b>' . zen_clean_html($fInfo->categories_name) . '"</b>'];
                            $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_FEATURED_CATEGORIES, 'fID=' . $fInfo->featured_categories_id . $page_search_parameters) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                            break;

                        case 'pre_add':
                            $heading[] = ['text' => '<h4>' . TEXT_INFO_HEADING_PRE_ADD_FEATURED . '</h4>'];
                            $contents = ['form' => zen_draw_form('featured', FILENAME_FEATURED_CATEGORIES, 'action=pre_add_confirmation' . $page_search_parameters, 'post', 'class="form-horizontal"')];
                            $contents[] = ['text' => TEXT_INFO_PRE_ADD_INTRO];
                            $result = $db->Execute("SELECT MAX(categories_id) AS lastcategoryid FROM " . TABLE_CATEGORIES);
                            $max_category_id = $result->fields['lastcategoryid'];
                            $contents[] = ['text' => zen_draw_input_field('pre_add_categories_id', '', zen_set_field_length(TABLE_FEATURED_CATEGORIES, 'categories_id') . ' class="form-control" id="pre_add_categories_id" required max="' . $max_category_id . '"', '', 'number')];
                            $contents[] = ['align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_CONFIRM . '</button> <a href="' . zen_href_link(FILENAME_FEATURED_CATEGORIES, (!empty($fInfo->featured_categories_id) ? '&fID=' . $fInfo->featured_categories_id : '') . $page_search_parameters) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                            break;

                        default:
                            if (isset($fInfo) && is_object($fInfo)) {
                                $heading[] = ['text' => '<h4>ID#' . $fInfo->categories_id . ': ' . zen_clean_html($fInfo->categories_name) . '"</h4>'];
                                $contents[] = [
                                'align' => 'text-center',
                                'text' => '
                                <a href="' . zen_href_link(FILENAME_FEATURED_CATEGORIES, '&fID=' . $fInfo->featured_categories_id . '&action=edit' . $page_search_parameters) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>
                                <a href="' . zen_href_link(FILENAME_FEATURED_CATEGORIES, '&fID=' . $fInfo->featured_categories_id . '&action=delete' . $page_search_parameters) . '" class="btn btn-warning" role="button">' . TEXT_INFO_HEADING_DELETE_FEATURED . '</a>'
                                ];
                                $contents[] = ['text' => TEXT_FEATURED_AVAILABLE_DATE . ' ' . (($fInfo->featured_date_available !== '0001-01-01' && $fInfo->featured_date_available !== '') ? zen_date_short($fInfo->featured_date_available) : TEXT_NONE)];
                                $contents[] = ['text' => TEXT_FEATURED_EXPIRES_DATE . ' ' . (($fInfo->expires_date !== '0001-01-01' && $fInfo->expires_date !== '') ? zen_date_short($fInfo->expires_date) : TEXT_NONE)];
                                if ($fInfo->date_status_change !== null && $fInfo->date_status_change !== '0001-01-01 00:00:00') {
                                    $contents[] = ['text' => TEXT_INFO_STATUS_CHANGED . ' ' . zen_date_short($fInfo->date_status_change)];
                                }
                                $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($fInfo->featured_last_modified)];
                                $contents[] = ['text' => TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($fInfo->featured_date_added)];
                                $contents[] = [
                                'align' => 'text-center',
                                'text' => zen_info_image($fInfo->categories_image, htmlspecialchars($fInfo->categories_name, ENT_COMPAT, CHARSET, true), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)
                                ];
                                $contents[] = [
                                'align' => 'text-center',
                                'text' => '<a href="' . zen_href_link(FILENAME_CATEGORIES, '&cPath=' . $fInfo->parent_id . '&cID=' . $fInfo->categories_id . '&action=edit_category' ) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT_CATEGORY . '</a>'
                                ];
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
            <?php } ?>
            <!-- body_text_eof //-->
            <!-- body_eof //-->
        </div>
    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <!-- script for datepicker -->
    <script>
        $(function () {
            $('input[name="featured_date_available"]').datepicker({
                minDate: 0
            });
            $('input[name="expires_date"]').datepicker({
                minDate: 1
            });
        })
    </script>
    </body>
</html>
<?php
require DIR_WS_INCLUDES . 'application_bottom.php';

