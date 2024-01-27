<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (!empty($action)) {
    switch ($action) {
        case 'insert':
        case 'save':
            if (isset($_GET['mID'])) {
                $type_id = zen_db_prepare_input($_GET['mID']);
            }
            if (isset($_POST['type_ext'])) {
                $type_ext = zen_db_prepare_input($_POST['type_ext']);
            }
            if (isset($_POST['type_name'])) {
                $type_name = zen_db_prepare_input($_POST['type_name']);
            }

            $sql_data_array = ['type_ext' => $type_ext];

            if ($action == 'insert') {
                $insert_data_array = ['type_name' => $type_name];

                $sql_data_array = array_merge($sql_data_array, $insert_data_array);

                zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array);
                $type_id = zen_db_insert_id();
            } elseif ($action == 'save') {
                $insert_data_array = ['type_name' => $type_name];

                $sql_data_array = array_merge($sql_data_array, $insert_data_array);

                zen_db_perform(TABLE_MEDIA_TYPES, $sql_data_array, 'update', "type_id = " . (int)$type_id);
            }

            zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $type_id));
            break;
        case 'deleteconfirm':
            $type_id = zen_db_prepare_input($_POST['mID']);

            $db->Execute("delete from " . TABLE_MEDIA_TYPES . "
                      where type_id = " . (int)$type_id);


            zen_redirect(zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page']));
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
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
        <!-- body //-->
        <h1 class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
                <table class="table table-hover table-striped">
                    <thead>
                    <tr class="dataTableHeadingRow">
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE; ?></th>
                        <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_MEDIA_TYPE_EXT; ?></th>
                        <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $media_type_query_raw = "SELECT *
                                         FROM " . TABLE_MEDIA_TYPES . "
                                         ORDER BY type_name";
                    $media_type_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $media_type_query_raw, $media_type_query_numrows);
                    $media_types = $db->Execute($media_type_query_raw);
                    $mType_parameter = '';
                    foreach ($media_types as $media_type) {
                        if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $media_type['type_id']))) && !isset($mInfo) && (substr($action, 0, 3) != 'new')) {
                            $mInfo = new objectInfo($media_type);
                            $mType_parameter = '&mID=' . $mInfo->type_id;
                        }

                        if (isset($mInfo) && is_object($mInfo) && ($media_type['type_id'] == $mInfo->type_id)) {
                            ?>
                            <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type['type_id'] . '&action=edit'); ?>'">
                        <?php } else { ?>
                            <tr class="dataTableRow" onclick="document.location.href='<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&mID=' . $media_type['type_id'] . '&action=edit'); ?>'">
                        <?php } ?>
                        <td class="dataTableContent"><?php echo $media_type['type_name']; ?></td>
                        <td class="dataTableContent"><?php echo $media_type['type_ext']; ?></td>
                        <td class="dataTableContent text-right">
                            <?php
                            if (isset($mInfo) && is_object($mInfo) && ($media_type['type_id'] == $mInfo->type_id)) {
                                echo zen_icon('caret-right', '', '2x', true);;
                            } else {
                                echo '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, zen_get_all_get_params(['mID']) . 'mID=' . $media_type['type_id']) . '" data-toggle="tooltip" title="' . IMAGE_ICON_INFO . '" role="button">' . zen_icon('circle-info', '', '2x', true, false) . '</a>';
                            }
                            ?>
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
                        $heading[] = ['text' => '<h4>' . TEXT_HEADING_NEW_MEDIA_TYPE . '</h4>'];

                        $contents = ['form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data"')];
                        $contents[] = ['text' => TEXT_NEW_INTRO];
                        $contents[] = ['text' => zen_draw_label(TEXT_MEDIA_TYPE_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name') . ' class="form-control"')];
                        $contents[] = ['text' => zen_draw_label(TEXT_MEDIA_TYPE_EXT, 'type_ext', 'class="control-label"') . '<br>' . zen_draw_input_field('type_ext', '', zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext') . ' class="form-control"')];
                        $contents[] = ['align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . (isset($_GET['mID']) ? '&mID=' . $_GET['mID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;
                    case 'edit':
                        $heading[] = ['text' => '<h4>' . TEXT_HEADING_EDIT_MEDIA_TYPE . '</h4>'];
                        $contents = ['form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter . '&action=save', 'post', 'enctype="multipart/form-data"')];
                        $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
                        $contents[] = ['text' => zen_draw_label(TEXT_MEDIA_TYPE_NAME, 'type_name', 'class="control-label"') . zen_draw_input_field('type_name', $mInfo->type_name, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_name') . ' class="form-control"')];
                        $contents[] = ['text' => zen_draw_label(TEXT_MEDIA_TYPE_EXT, 'type_ext', 'class="control-label"') . zen_draw_input_field('type_ext', $mInfo->type_ext, zen_set_field_length(TABLE_MEDIA_TYPES, 'type_ext') . ' class="form-control"')];
                        $contents[] = ['align' => 'center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;
                    case 'delete':
                        $heading[] = ['text' => '<h4>' . TEXT_HEADING_DELETE_MEDIA_TYPES . '</h4>'];

                        $contents = ['form' => zen_draw_form('media_type', FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $mInfo->type_id)];
                        $contents[] = ['text' => TEXT_DELETE_INTRO];
                        $contents[] = ['text' => '<br><b>' . $mInfo->type_name . '</b>'];

                        $contents[] = ['align' => 'center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>'];
                        break;
                    default:
                        if (isset($mInfo) && is_object($mInfo)) {
                            $heading[] = ['text' => '<h4>' . $mInfo->type_name . '</h4>'];

                            $contents[] = ['align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>'];
                            $contents[] = ['text' => '<br>' . TEXT_EXTENSION . ' ' . $mInfo->type_ext];
                        }
                        break;
                }

                if (!empty($heading) && !empty($contents)) {
                    $box = new box;
                    echo $box->infoBox($heading, $contents);
                }
                ?>
            </div>
        </div>
        <table class="table">
            <tr>
                <td><?php echo $media_type_split->display_count($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_MEDIA_TYPES); ?></td>
                <td class="text-right"><?php echo $media_type_split->display_links($media_type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
            </tr>
            <?php if (empty($action)) { ?>
                <tr>
                    <td colspan="2" class="text-right"><a
                            href="<?php echo zen_href_link(FILENAME_MEDIA_TYPES, 'page=' . $_GET['page'] . $mType_parameter . '&action=new'); ?>"
                            class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
                </tr>
            <?php } ?>
        </table>
        <!-- body_text_eof //-->

        <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    </body>
    </html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');
