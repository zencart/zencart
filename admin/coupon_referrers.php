<?php

/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Feb 23 New in v2.0.0-beta1 $
 *
 * @var messageStack $messageStack
 */
require('includes/application_top.php');

$max_records_per_page = 75; // MAX_DISPLAY_SEARCH_RESULTS;
$max_display_page_links = MAX_DISPLAY_PAGE_LINKS;

$action = $_GET['action'] ?? '';

$href_page_param = (!empty($_GET['page']) && (int)$_GET['page'] !== 1) ? 'page=' . $_GET['page'] . '&' : '';

if (!empty($action)) {
    switch ($action) {
        case 'insert':
        case 'save':
            if (isset($_GET['rID'])) {
                $referrer_id = (int)$_GET['rID'];
            }
            $referrer_domain = zen_db_prepare_input($_POST['referrer_domain']);
            $coupon_id = (int)$_POST['coupon_id'];
            $error = false;

            if (empty($referrer_domain)) {
                $messageStack->add(ERROR_REFERRER_DOMAIN_UNIQUE_REQUIRED);
                $error = true;
            } else {
                $sql = 'SELECT * FROM ' . TABLE_COUPON_REFERRERS . ' WHERE referrer_domain = :domain';
                $sql = $db->bindVars($sql, ':domain', $referrer_domain, 'string');
                $result = $db->Execute($sql, 1);
                if (!$result->EOF) {
                    $messageStack->add(ERROR_REFERRER_DOMAIN_ALREADY_EXISTS);
                    $error = true;
                }
            }
            if (empty($coupon_id)) {
                $messageStack->add(ERROR_COUPON_SELECTION_REQUIRED);
                $error = true;
            } else {
                $sql = 'SELECT * FROM ' . TABLE_COUPONS . ' WHERE coupon_id = :coupon_id';
                $sql = $db->bindVars($sql, ':coupon_id', $coupon_id, 'integer');
                $result = $db->Execute($sql, 1);
                if ($result->EOF) {
                    $messageStack->add(ERROR_INVALID_COUPON_SPECIFIED);
                    $error = true;
                }
            }

            if (!$error && $referrer_domain && $coupon_id) {
                $sql_data_array = [
                    'referrer_domain' => $referrer_domain,
                    'coupon_id' => $coupon_id,
                ];
                if ($action === 'insert') {
                    zen_db_perform(TABLE_COUPON_REFERRERS, $sql_data_array);
                    $referrer_id = $db->insert_ID();
                } elseif ($action === 'save') {
                    zen_db_perform(TABLE_COUPON_REFERRERS, $sql_data_array, 'update', "referrer_id = " . (int)$referrer_id);
                }
            }
            if (!$error) {
                zen_redirect(zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . (!empty($referrer_id) ? 'rID=' . $referrer_id : '')));
            } else {
                $action = match ($action) {
                    'insert' => 'new',
                    'save' => 'edit',
                };
            }
            break;

        case 'deleteconfirm':
            $referrer_id = $_POST['rID'];
            if (!empty($referrer_id)) {
                $db->Execute("DELETE FROM " . TABLE_COUPON_REFERRERS . " WHERE referrer_id = " . (int)$referrer_id);
            }
            zen_redirect(zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param));
            break;
    }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
</head>
<body class="couponReferrers">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid">
    <h1><?php echo HEADING_TITLE; ?></h1>
    <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
            <table class="table table-hover" role="listbox">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="dataTableHeadingContent text-center"><?php echo TABLE_HEADING_ID; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_REFERRER_DOMAIN; ?></th>
                    <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_REFERRER_COUPON_NAME; ?></th>
                    <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = "SELECT r.*, c.coupon_code, c.coupon_amount
                        FROM " . TABLE_COUPON_REFERRERS . ' r
                        LEFT OUTER JOIN ' . TABLE_COUPONS . ' c ON (r.coupon_id = c.coupon_id)';

                // Split Page
                // reset page when page is unknown
                if ((empty($_GET['page']) || (int)$_GET['page'] === 1) && !empty($_GET['rID'])) {
                    $check_page = $db->Execute($sql);
                    $check_count = 0;
                    if ($check_page->RecordCount() > $max_records_per_page) {
                        foreach ($check_page as $item) {
                            if ((int)$item['referrer_id'] === (int)$_GET['rID']) {
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

                $referrers_split = new splitPageResults($_GET['page'], $max_records_per_page, $sql, $referrers_query_numrows);
                $referrers = $db->Execute($sql);

                if (!empty($referrers) && count($referrers) === 1 && empty($referrers->fields['referrer_id'])) {
                    $groups = [];
                    echo '<tr><td colspan="4" class="text-center"><strong>' . TEXT_NO_REFERRERS_FOUND . '</strong></td></tr>';
                }

                foreach ($referrers as $referrer) {
                    if ((!isset($_GET['rID']) || (isset($_GET['rID']) && $_GET['rID'] == $referrer['referrer_id'])) && !isset($rInfo) && $action !== 'new') {
                        $rInfo = new objectInfo($referrer);
                    }

                $class_and_id = 'class="dataTableRow"';
                $role = 'role="option" aria-selected="false"';
                if (isset($rInfo) && is_object($rInfo) && ($referrer['referrer_id'] == $rInfo->referrer_id)) {
                    $class_and_id = 'id="defaultSelected" class="dataTableRowSelected"';
                    $role = 'role="option" aria-selected="true"';
                }
                ?>
                <tr <?php echo $class_and_id; ?> onclick="document.location.href='<?php echo zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . 'rID=' . $referrer['referrer_id']); ?>'" <?php echo $role;?>>
                    <td class="dataTableContent text-center"><?php echo $referrer['referrer_id']; ?></td>
                    <td class="dataTableContent"><?php echo $referrer['referrer_domain']; ?></td>
                    <td class="dataTableContent"><?php
                        if (empty($referrer['coupon_id'])) {
                            echo TEXT_NONE;
                        } else {
                            // link to view coupon details
                            echo '<a href="' . zen_href_link(FILENAME_COUPON_ADMIN, 'cid=' . $referrer['coupon_id'] . '&action=edit') . '"class="btn btn-sm btn-default" data-toggle="tooltip" title="' . IMAGE_VIEW . '">' . zen_icon('preview', hidden: true) . '</a>&nbsp;';
                            // display coupon code
                            echo $referrer['coupon_code'];
                        }
                        ?>
                    </td>


                    <td class="dataTableContent text-right actions">
                        <div class="btn-group">
                            <?php echo '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $referrer['referrer_id'] . '&action=edit') . '" class="btn btn-sm btn-default btn-edit" data-toggle="tooltip" title="' . ICON_EDIT . '">' . zen_icon('pencil', hidden: true) . '</a>'; ?>
                            <?php echo '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $referrer['referrer_id'] . '&action=delete') . '" class="btn btn-sm btn-default btn-delete" data-toggle="tooltip" title="' . ICON_DELETE . '">' . zen_icon('trash', hidden: true) . '</a>'; ?>
                        </div>
                        <?php
                        if (isset($rInfo) && is_object($rInfo) && ($referrer['referrer_id'] == $rInfo->referrer_id)) {
                            echo zen_icon('caret-right', '', '2x', true);
                        } else {
                            echo '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, zen_get_all_get_params(['rID']) . 'rID=' . $referrer['referrer_id']) . '">' .
                                zen_icon('circle-info', IMAGE_ICON_INFO, '2x', true, false) .
                                '</a>';
                        }
                        ?>
                    </td>
                    <?php
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];

            $coupon_array = [['id' => '', 'text' => PLEASE_SELECT]];
            $coupons = Coupon::getAllCouponsByName();
            foreach ($coupons as $coupon) {
                $coupon_array[] = [
                    'id' => $coupon['coupon_id'],
                    'text' => $coupon['coupon_code'] . ' (' . $coupon['coupon_name'] . ')',
                ];
            }

            switch ($action) {
                case 'new':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_NEW_REFERRER . '</h4>'];

                    $contents = ['form' => zen_draw_form('referrer_form', FILENAME_COUPON_REFERRERS, 'action=insert', 'post', 'class="form-horizontal"')];
                    $contents[] = ['text' => TEXT_NEW_INTRO];
                    $contents[] = [
                        'text' => '<br>' . zen_draw_label(TEXT_REFERRER_DOMAIN, 'referrer_domain', 'class="control-label"') .
                                  zen_draw_input_field('referrer_domain', $referrer_domain ?? '', zen_set_field_length(TABLE_COUPON_REFERRERS, 'referrer_domain') . ' class="form-control"', true),
                    ];
                    $contents[] = [
                        'text' => '<br>' . zen_draw_label(TEXT_SELECT_A_COUPON, 'coupon_id', 'class="control-label"') .
                                  zen_draw_pull_down_menu('coupon_id', $coupon_array, $coupon_id ?? '', 'class="form-control"', true),
                    ];
                    $contents[] = [
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button>' .
                                  '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . (isset($_GET['rID']) ? '&rID=' . $_GET['rID'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>',
                    ];
                    break;
                case 'edit':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_EDIT_REFERRER . '</h4>'];

                    $contents = [
                        'form' => zen_draw_form('referrer_form', FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $rInfo->referrer_id . '&action=save', 'post', 'class="form-horizontal"'),
                    ];
                    $contents[] = ['text' => TEXT_INFO_EDIT_INTRO];
                    $contents[] = [
                        'text' => '<br>' . zen_draw_label(TEXT_REFERRER_DOMAIN, 'referrer_domain', 'class="control-label"') .
                            zen_draw_input_field('referrer_domain', htmlspecialchars($rInfo->referrer_domain, ENT_COMPAT, CHARSET, true), zen_set_field_length(TABLE_COUPON_REFERRERS, 'referrer_domain') . ' class="form-control"', true),
                    ];
                    $contents[] = [
                        'text' => '<br>' . zen_draw_label(TEXT_SELECT_A_COUPON, 'coupon_id', 'class="control-label"') .
                                  zen_draw_pull_down_menu('coupon_id', $coupon_array, $rInfo->coupon_id, 'class="form-control"', true),
                    ];
                    $contents[] = [
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button>' .
                                  '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $rInfo->referrer_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>',
                    ];
                    break;
                case 'delete':
                    $heading[] = ['text' => '<h4>' . TEXT_HEADING_DELETE_REFERRER . '</h4>'];

                    $contents = [
                        'form' => zen_draw_form('referrer_form', FILENAME_COUPON_REFERRERS, $href_page_param . '&action=deleteconfirm') .
                                  zen_draw_hidden_field('rID', $rInfo->referrer_id),
                    ];
                    $contents[] = ['text' => TEXT_DELETE_INTRO];
                    $contents[] = ['text' => '<br><b>' . $rInfo->referrer_domain . '</b>'];

                    $contents[] = [
                        'align' => 'text-center',
                        'text' => '<br><button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button>' .
                                  '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $rInfo->referrer_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>',
                        ];
                    break;
                default:
                    if (isset($rInfo) && is_object($rInfo)) {
                        $heading[] = ['text' => '<h4>' . $rInfo->referrer_domain . '</h4>'];

                        $contents[] = [
                            'align' => 'text-center',
                            'text' => '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $rInfo->referrer_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a>' .
                                      '<a href="' . zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . '&rID=' . $rInfo->referrer_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>',
                        ];
                        $contents[] = ['text' => '<br>' . TEXT_INFO_DATE_ADDED . ' ' . zen_date_short($rInfo->date_added)];
                        if (!empty($rInfo->updated_at)) {
                            $contents[] = ['text' => TEXT_INFO_LAST_MODIFIED . ' ' . zen_date_short($rInfo->updated_at)];
                        }
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
                <td><?php echo $referrers_split->display_count($referrers_query_numrows, $max_records_per_page, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_REFERRERS); ?></td>
                <td class="text-right"><?php echo $referrers_split->display_links($referrers_query_numrows, $max_records_per_page, $max_display_page_links, $_GET['page']); ?></td>
            </tr>
        </table>
    </div>
    <?php
    if (empty($action)) {
        ?>
        <div class="text-right">
            <a href="<?php echo zen_href_link(FILENAME_COUPON_REFERRERS, $href_page_param . 'action=new'); ?>" class="btn btn-primary" role="button">
                <?php echo IMAGE_INSERT; ?>
            </a>
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
