<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: ZenExpert 2026-04-06 Modified in v3.0.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
    $messageStack->add('STRICT ERROR REPORTING IS ON', 'error');
}

/*
 * pull in any necessary JS for the page
 * Left here for legacy pages that do not use the new admin_html_head.php file
 */
require_once DIR_WS_INCLUDES . 'javascript_loader.php';

// -----
// Admin Framework Incompatibility Alerting for old addons:
// If the current page-load did not use the admin_html_head.php for the CSS files'
// loading, let the admin know via message and log a PHP Deprecated issue ... once for
// each page during an admin's session.
//
// Note: This section will be removed in a future version of Zen Cart!
//
if (!isset($zen_admin_html_head_loaded) && !isset($_SESSION['pages_needing_update'][$current_page])) {
    $_SESSION['pages_needing_update'][$current_page] = true;
    $messageStack->add(WARNING_PAGE_REQUIRES_UPDATE, 'warning');
    trigger_error(WARNING_PAGE_REQUIRES_UPDATE, E_USER_DEPRECATED);
}

// Show Languages Dropdown for convenience only if main filename and directory exists
$languages_array = [];
$languages = zen_get_languages();
if (empty($action) && count($languages) > 1) {
    $languages_selected = $_SESSION['language'];
    $missing_languages = '';
    $count = 0;
    for ($i = 0, $n = count($languages); $i < $n; $i++) {
        $test_directory = DIR_WS_LANGUAGES . $languages[$i]['directory'];
        $test_file = DIR_WS_LANGUAGES . 'lang.' . $languages[$i]['directory'] . '.php';
        if (file_exists($test_file) && file_exists($test_directory)) {
            $count++;
            $languages_array[] = array('id' => $languages[$i]['code'], 'text' => $languages[$i]['name']);
        } else {
            $missing_languages .= ' ' . ucfirst($languages[$i]['directory']) . ' ' . $languages[$i]['name'];
        }
    }
    if ($count != count($languages)) {
        $messageStack->add('MISSING LANGUAGE FILES OR DIRECTORIES ...' . $missing_languages, 'caution');
    }
}

// gv queue check
$new_gv_queue_cnt = 0;
if (defined('MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN') && MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN === 'true' && (zen_is_superuser() || check_page(FILENAME_GV_QUEUE, ''))) {
    $new_gv_queue = $db->Execute("SELECT * FROM " . TABLE_COUPON_GV_QUEUE . " WHERE release_flag='N'");
    if ($new_gv_queue->RecordCount() > 0) {
        $new_gv_queue_cnt = $new_gv_queue->RecordCount();
        $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE) . '">' . '<span class="btn btn-info">' . IMAGE_GIFT_QUEUE . '</span></a>';
    }
}

// prepare admin info for dropdown
$admin_ip = $_SERVER['REMOTE_ADDR'];
$admin_host = gethostname();
$admin_tz = date_default_timezone_get();
$admin_locale = setlocale(LC_TIME, 0);

$upperMenuArray = [
        [ 'a' => zen_href_link(FILENAME_DEFAULT), 'title' => HEADER_TITLE_TOP ],
        [ 'a' => zen_catalog_href_link(FILENAME_DEFAULT), 'title' => HEADER_TITLE_ONLINE_CATALOG, 'params' => 'target="_blank" rel="noopener"' ],
        [ 'a' => 'https://www.zen-cart.com/forum', 'title' => HEADER_TITLE_SUPPORT_SITE ],
        [ 'a' => zen_href_link(FILENAME_SERVER_INFO), 'title' => HEADER_TITLE_VERSION ],
        [ 'a' => zen_href_link(FILENAME_ADMIN_ACCOUNT), 'title' => HEADER_TITLE_ACCOUNT ],
        [ 'a' => zen_href_link(FILENAME_LOGOFF), 'title' => HEADER_TITLE_LOGOFF ],
];
$upperMenuOverrideArray = '';
$zco_notifier->notify('NOTIFY_ADMIN_HEADER_UPPERMENU', $upperMenuArray, $upperMenuOverrideArray);
if (!empty($upperMenuOverrideArray) && is_array($upperMenuOverrideArray)) {
    $upperMenuArray = $upperMenuOverrideArray;
}
// Filter out core links to dynamically append plugin-added links
$core_links = [
        zen_href_link(FILENAME_DEFAULT),
        zen_catalog_href_link(FILENAME_DEFAULT),
        'https://www.zen-cart.com/forum',
        zen_href_link(FILENAME_SERVER_INFO),
        zen_href_link(FILENAME_ADMIN_ACCOUNT),
        zen_href_link(FILENAME_LOGOFF)
];
$plugin_menu_items = [];
foreach ($upperMenuArray as $menuItem) {
    if (!in_array($menuItem['a'], $core_links)) {
        $plugin_menu_items[] = $menuItem;
    }
}
?>

    <nav class="navbar navbar-inverse navbar-fixed-top top-tier">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#top-bar-collapse">
                    <span class="sr-only"><?= HEADER_TOGGLE_NAVIGATION ?></span>
                    <i class="fa fa-ellipsis-v"></i>
                </button>
                <?php if (defined('HEADER_LOGO_IMAGE_HOME') && HEADER_LOGO_IMAGE_HOME !== '') { ?>
                    <a class="navbar-brand" href="<?= zen_href_link(FILENAME_DEFAULT) ?>" style="padding: 10px;">
                        <?= zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE_HOME, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT, 'class="img-responsive object-fit-contain" style="max-height: 40px;"') ?>
                    </a>
                <?php } else { ?>
                    <a class="navbar-brand" href="<?= zen_href_link(FILENAME_DEFAULT) ?>">
                        <i class="fa fa-home"></i> <?= STORE_NAME ?>
                        <small class="text-muted"><?= HEADER_TEXT_ADMIN ?></small>
                    </a>
                <?php } ?>
            </div>

            <div class="collapse navbar-collapse" id="top-bar-collapse">
                <ul class="nav navbar-nav navbar-left">
                    <?php if (zen_is_superuser() || check_page(FILENAME_ORDERS, '')) { ?>
                    <li class="hidden-xs">
                        <?= zen_draw_form('order_search', FILENAME_ORDERS, '', 'get', 'class="navbar-form"', true) ?>
                        <div class="form-group header-search">
                        <?= zen_draw_input_field('oID', '', 'id="oIDsearch" class="form-control" placeholder="' . HEADER_TEXT_SEARCH_ORDERS . '"', false, 'search') ?>
                        <?= zen_draw_hidden_field('action', 'edit') ?>
                        </div>
                        <?= '</form>' ?>
                    </li>
                    <?php } ?>

                    <?php if (false && zen_is_superuser() || check_page(FILENAME_CUSTOMERS, '')) { ?>
                    <li class="hidden-xs">
                        <?= zen_draw_form('customer_search', FILENAME_CUSTOMERS, '', 'get', 'class="navbar-form"', true); ?>
                        <div class="form-group header-search">
                        <?= zen_draw_input_field('search', '', 'id="cIDsearch" class="form-control" placeholder="' . HEADER_TEXT_SEARCH_CUSTOMERS . '"', false, 'search'); ?>
                        </div>
                        <?= '</form>' ?>
                    </li>
                    <?php } ?>

                    <?php if (false && zen_is_superuser() || check_page(FILENAME_CATEGORY_PRODUCT_LISTING, '')) { ?>
                    <li class="hidden-xs">
                        <?= zen_draw_form('goto', FILENAME_CATEGORY_PRODUCT_LISTING, '', 'get', 'class="navbar-form"') ?>
                        <div class="form-group header-search goto-category">
                            <small class="text-muted"><?= HEADER_TEXT_JUMP_TO_CATEGORY ?>><br></small>
                        <?= zen_draw_pull_down_menu('cPath', zen_get_category_tree(), $current_category_id, 'onchange="this.form.submit();" class="form-control" id="cPath-search"') ?>
                        <?= '</form>' ?>
                    </li>
                    <?php } ?>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <div class="currentTime">
                        <?= mb_convert_encoding($zcDate->output(str_replace('(%z)', '', ADMIN_NAV_DATE_TIME_FORMAT), time()), 'UTF-8') ?>
                        <br><small><?= date_default_timezone_get() ?> <?= $zcDate->output(' (%z)', time()) ?></small>
                        </div>
                    </li>

                    <li class="hidden-xs">
                        <a href="<?= zen_href_link(FILENAME_DEFAULT) ?>" title="<?= HEADER_TITLE_TOP ?>">
                            <i class="fa fa-home"></i> <?= HEADER_TITLE_TOP ?>
                        </a>
                    </li>

                    <?php if (!empty($new_gv_queue_cnt)) { ?>
                        <li>
                            <a href="<?= zen_href_link(FILENAME_GV_QUEUE) ?>" title="<?= strip_tags(IMAGE_GIFT_QUEUE) ?>">
                                <i class="fa fa-gift"></i>
                                <span class="badge"><?= $new_gv_queue_cnt ?></span>
                            </a>
                        </li>
                    <?php } ?>

                    <li>
                        <a href="<?= zen_catalog_href_link(FILENAME_DEFAULT) ?>" target="_blank" title="<?= HEADER_TITLE_ONLINE_CATALOG ?>" rel="noopener">
                            <i class="fa fa-store"></i> <?= HEADER_TITLE_ONLINE_CATALOG ?>
                        </a>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?= HEADER_TITLE_VERSION ?>">
                            <i id="versionCheckPill" class="fa fa-server"></i> <span class="visible-xs-inline"> <?= HEADER_TITLE_VERSION ?></span>
                            <span id="versionCheckNotifyBadge" class="badge-notify" style="display:none"></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <div class="version-dropdown-content">
                                    <h5>
                                        <?= HEADER_TITLE_VERSION_SYSTEM_CHECK ?>
                                    </h5>
                                    <div id="versionCheckAlert"></div>
                                </div>
                                <div class="version-dropdown-footer" id="versionCheckFooter">
                                    <?= TEXT_CURRENT_VER_IS . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') ?>
                                </div>
                            </li>
                        </ul>
                    </li>

                    <?php if (!empty($languages_array)) { ?>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-flag"></i> <span class="visible-xs-inline"> <?= HEADER_TEXT_LANGUAGES ?></span> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <?php foreach($languages_array as $lang) { ?>
                                    <li><a href="<?= zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('language', 'action')) . 'language=' . $lang['id']) ?>"><?= $lang['text'] ?></a></li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="user-avatar"></span>
                            <?= zen_output_string_protected(zen_get_admin_name($_SESSION['admin_id'])) ?>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= zen_href_link(FILENAME_ADMIN_ACCOUNT, '', 'NONSSL') ?>"><i class="fa fa-user"></i> <?= HEADER_TITLE_ACCOUNT ?></a></li>
                            <li><a href="<?= zen_href_link(FILENAME_SERVER_INFO, '', 'NONSSL') ?>"><i class="fa fa-info-circle"></i> <?= HEADER_TITLE_VERSION ?></a></li>

                            <?php if (!empty($plugin_menu_items)) { ?>
                                <li class="divider"></li>
                                <?php foreach ($plugin_menu_items as $item) { ?>
                                <li <?= $item['id'] ? 'id="' . $item['id'] . '"' : '' ?>  <?= $item['li-class'] ? 'class="' . $item['li-class'] . '"' : '' ?>><a href="<?= $item['a'] ?>" <?= $item['params'] ?? '' ?>><i class="fa <?= $item['icon'] ?? 'fa-plug' ?>"></i> <?= $item['title'] ?></a></li>
                                <?php } ?>
                            <?php } ?>

                            <li class="divider"></li>
                            <li class="header-info-menu">
                                <span class="info-label"><?= HEADER_TEXT_IP_ADDRESS ?></span>
                                <span class="info-val"><?= $admin_ip ?></span>

                                <span class="info-label"><?= HEADER_TEXT_HOSTNAME ?></span>
                                <span class="info-val"><?= $admin_host ?></span>

                                <span class="info-label"><?= HEADER_TEXT_TIMEZONE ?></span>
                                <span class="info-val"><?= $admin_tz ?></span>

                                <span class="info-label"><?= HEADER_TEXT_LOCALE ?></span>
                                <span class="info-val"><?= $admin_locale ?></span>
                            </li>

                            <li class="divider"></li>
                            <li><a href="https://www.zen-cart.com/forum"><i class="fa fa-info-circle"></i> <?= HEADER_TITLE_SUPPORT_SITE ?></a></li>
                            <li><a href="<?= zen_href_link(FILENAME_LOGOFF, '', 'NONSSL') ?>"><i class="fa fa-sign-out"></i> <?= HEADER_TITLE_LOGOFF ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div style="height: 50px;"></div>

<?php require DIR_WS_INCLUDES . 'header_navigation.php'; ?>

<?php if (zen_is_superuser() || check_page(FILENAME_ADMIN_ACTIVITY, '')) { ?>
    <div class="container-fluid admin-alerts-wrapper noprint">
        <?php if (isset($_SESSION['reset_admin_activity_log']) && ($_SESSION['reset_admin_activity_log'] == true && (basename($PHP_SELF) == FILENAME_DEFAULT . '.php'))) { ?>
            <div class="alert alert-danger text-center mb-3">
                <strong><?= HEADER_TEXT_SECURITY_WARNING ?></strong><br>
                <?= RESET_ADMIN_ACTIVITY_LOG ?><br>
                <a class="btn btn-warning btn-xs mt-1" role="button" href="<?= zen_href_link(FILENAME_ADMIN_ACTIVITY) ?>">
                    <?= TEXT_BUTTON_RESET_ACTIVITY_LOG;?>
                </a>
            </div>
        <?php } ?>

    </div>
<?php } ?>

<?php if ($messageStack->size > 0) { ?>
    <div class="container-fluid mb-3">
        <?= $messageStack->output() ?>
    </div>
<?php } ?>
