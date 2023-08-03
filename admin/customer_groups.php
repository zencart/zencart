<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Aug 13 Modified in v1.5.8-alpha2 $
 */
require('includes/application_top.php');
$max_records_per_page = 75; // MAX_DISPLAY_SEARCH_RESULTS;
$max_display_page_links = MAX_DISPLAY_PAGE_LINKS;

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$href_page_param = (!empty($_GET['page']) && (int)$_GET['page'] !== 1) ? 'page=' . $_GET['page'] . '&' : '';

if (!empty($action)) {
    switch ($action) {
        case 'insert':
            if (!empty($_POST['group_name'])) {
                $group_id = zen_create_customer_group($_POST['group_name'], $_POST['group_comment']);
            }
            zen_redirect(zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $group_id));
            break;
        case 'save':
            if (isset($_GET['gID'])) {
                $group_id = (int)$_GET['gID'];
                zen_update_customer_group($group_id, $_POST);
            }
            zen_redirect(zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $group_id));
            break;
        case 'deleteconfirm':
            $delete_cust_confirmed = (isset($_POST['delete_even_with_customers']) && $_POST['delete_even_with_customers'] == 'on');
            $group_id = (int)$_POST['gID'];

            $result = zen_delete_customer_group($group_id, $delete_cust_confirmed);

            if (is_string($result)) {
                $messageStack->add_session(ERROR_GROUP_STILL_HAS_CUSTOMERS, 'error');
            }
            zen_redirect(zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . (!empty($group_id) ?  'gID=' . $group_id : '')));
            break;
    }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body class="customerGroups">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ID; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_NAME; ?></th>
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_GROUP_CUSTOMER_COUNT; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_GROUP_COMMENTS; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT cg.*, count(ctg.customer_id) as customer_count
                        FROM " . TABLE_CUSTOMER_GROUPS . " cg
                        LEFT JOIN " . TABLE_CUSTOMERS_TO_GROUPS . " ctg USING (group_id)
                        GROUP BY cg.group_id
                        ORDER BY group_name, group_id";

                // Split Page
                // reset page when page is unknown
                if ((empty($_GET['page']) || $_GET['page'] == '1') && !empty($_GET['gID'])) {
                    $check_page = $db->Execute($sql);
                    $check_count = 0;
                    if ($check_page->RecordCount() > $max_records_per_page) {
                        foreach ($check_page as $item) {
                            if ((int)$item['group_id'] === (int)$_GET['gID']) {
                                break;
                            }
                            $check_count++;
                        }
                        $_GET['page'] = round((($check_count / $max_records_per_page) + (fmod_round($check_count, $max_records_per_page) != 0 ? .5 : 0)), 0);
                        $href_page_param = (!empty($_GET['page']) && (int)$_GET['page'] !== 1) ? 'page=' . $_GET['page'] . '&' : '';
                    } else {
                        $_GET['page'] = 1;
                    }
                }

                $groups_split = new splitPageResults($_GET['page'], $max_records_per_page, $sql, $groups_query_numrows);
                $groups = $db->Execute($sql);

                if (!empty($groups) && count($groups) === 1 && empty($groups->fields['group_id'])) {
                    $groups = [];
                    echo '<tr><td colspan="4" class="text-center"><strong>' . TEXT_NO_GROUPS_FOUND . '</strong></td></tr>';
                }

                foreach ($groups as $group) {
                    if ((!isset($_GET['gID']) || (isset($_GET['gID']) && ($_GET['gID'] == $group['group_id']))) && !isset($gInfo) && (substr($action, 0, 3) != 'new')) {
                        $gInfo = new objectInfo($group);
                    }

                    $class_and_id = 'class="dataTableRow"';
                    if (isset($gInfo) && is_object($gInfo) && ($group['group_id'] == $gInfo->group_id)) {
                        $class_and_id = 'id="defaultSelected" class="dataTableRowSelected"';
                    }
                    ?>
                    <tr <?php echo $class_and_id; ?> onclick="document.location.href='<?php echo zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $group['group_id'] . '&action=edit'); ?>'" role="button">
                        <td class="dataTableContent text-center"><?php echo $group['group_id']; ?></td>
                        <td class="dataTableContent"><?php echo $group['group_name']; ?></td>
                        <td class="dataTableContent text-center"><?php echo $group['customer_count']; ?></td>
                        <td class="dataTableContent"><?php echo $group['group_comment']; ?></td>
                        <td class="dataTableContent text-right"><div>
                            <?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $group['group_id'] . '&action=edit') . '" class="btn btn-primary" role="button">' . ICON_EDIT . '</a>'; ?>
                            <?php echo '<a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $group['group_id'] . '&action=delete') . '" class="btn btn-warning" role="button">' . ICON_DELETE . '</a>'; ?>
                            <?php 
                              if (!isset($gInfo) || (isset($gInfo) && is_object($gInfo) && ($group['group_id'] != $gInfo->group_id))) {
                                 echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); 
                              }
                             ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];

            switch ($action) {
                case 'new':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_ADD_GROUP . '</h4>'];

                    $contents = ['form' => zen_draw_form('group_add', FILENAME_CUSTOMER_GROUPS, 'action=insert', 'post', 'class="form-horizontal"')];
                    $contents[] = ['text' => TEXT_NEW_INTRO];
                    $contents[] = ['text' => '<br>' . zen_draw_label(TEXT_GROUP_NAME, 'group_name', 'class="control-label"') . zen_draw_input_field('group_name', '', zen_set_field_length(TABLE_CUSTOMER_GROUPS, 'group_name') . ' class="form-control"')];
                    $contents[] = ['text' => '<br>' . zen_draw_label(TEXT_GROUP_COMMENT, 'group_comment', 'class="control-label"') . zen_draw_input_field('group_comment', '', zen_set_field_length(TABLE_CUSTOMER_GROUPS, 'group_comment') . ' class="form-control"')];
                    $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . (!empty($_GET['gID']) ? 'gID=' . $_GET['gID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                    break;
                case 'edit':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_EDIT_GROUP . '</h4>'];

                    $contents = ['form' => zen_draw_form('group_edit', FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $gInfo->group_id . '&action=save', 'post', 'class="form-horizontal"')];
                    $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
                    $contents[] = ['text' => '<br>' . zen_draw_label(TEXT_GROUP_NAME, 'group_name', 'class="control-label"') . zen_draw_input_field('group_name', htmlspecialchars($gInfo->group_name, ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_CUSTOMER_GROUPS, 'group_name') . ' class="form-control"')];
                    $contents[] = ['text' => '<br>' . zen_draw_label(TEXT_GROUP_COMMENT, 'group_comment', 'class="control-label"') . zen_draw_input_field('group_comment', zen_output_string_protected($gInfo->group_comment), zen_set_field_length(TABLE_CUSTOMER_GROUPS, 'group_comment') . ' class="form-control"')];
                    $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $gInfo->group_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                    break;
                case 'delete':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_DELETE_GROUP . '</h4>'];

                    $contents = ['form' => zen_draw_form('group_delete', FILENAME_CUSTOMER_GROUPS, $href_page_param . 'action=deleteconfirm') . zen_draw_hidden_field('gID', $gInfo->group_id)];
                    $contents[] = ['text' => TEXT_DELETE_INTRO];
                    $contents[] = ['text' => '<br><b>' . $gInfo->group_name . '</b>'];

                    if ($gInfo->customer_count > 0) {
                        $contents[] = ['text' => '<br>' . zen_draw_checkbox_field('delete_even_with_customers') . ' ' . TEXT_DELETE_EVEN_IF_CUSTOMERS_ASSIGNED];
                        $contents[] = ['text' => '<br>' . sprintf(TEXT_DELETE_WARNING_GROUP_MEMBERS_EXIST, $gInfo->customer_count)];
                    }

                    $contents[] = ['align' => 'text-center', 'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button>
                                <a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $gInfo->group_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                    break;
                default:
                    if (isset($gInfo) && is_object($gInfo) && !empty($gInfo->group_name)) {
                        $heading[] = ['text' => '<h4>' . $gInfo->group_name . '</h4>'];

                        $contents[] = ['align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $gInfo->group_id . '&action=edit') . '"class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>
                                <a href="' . zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'gID=' . $gInfo->group_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                        $contents[] = ['text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($gInfo->date_added)];
                        if (!empty($gInfo->last_modified)) $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($gInfo->last_modified)];
                        $contents[] = ['text' => '<br>' . TEXT_CUSTOMERS_IN_GROUP . ' ' . $gInfo->customer_count];
                    }
                    break;
            }

            if (!empty($heading) && !empty($contents)) {
                $box = new box;
                echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
        <!-- body_text_eof //-->
    </div>
    <div class="row">
        <table class="table">
            <tr>
                <td><?php echo $groups_split->display_count($groups_query_numrows, $max_records_per_page, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GROUPS); ?></td>
                <td class="text-right"><?php echo $groups_split->display_links($groups_query_numrows, $max_records_per_page, $max_display_page_links, $_GET['page']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (empty($action)) {
        ?>
        <div class="text-right">
            <?php $href = zen_href_link(FILENAME_CUSTOMER_GROUPS, $href_page_param . 'action=new'); ?>
            <a href="<?php echo $href; ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a>
        </div>
        <?php
    }
    ?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
