<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @version Modern Dynamic Dashboard 2026
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

$version_check_requested = (isset($_GET['vcheck']) && $_GET['vcheck'] != '') ? true : false;

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

// version check setup
$version_from_ini = '';
$version_ini_sysinfo = '';
$version_ini_index_sysinfo = '';
if (!isset($version_check_sysinfo)) $version_check_sysinfo = false;
if (!isset($version_check_index)) $version_check_index = false;

$skip_file = DIR_FS_ADMIN . 'includes/local/skip_version_check.ini';
if (file_exists($skip_file) && $lines = @file($skip_file)) {
    foreach ($lines as $line) {
        if (substr(trim($line), 0, 14) == 'version_check=') $version_from_ini = substr(trim(strtolower(str_replace('version_check=', '', $line))), 0, 3);
    }
}

$doVersionCheck = false;
$versionCheckError = false;
$system_update_available = false;

if ((SHOW_VERSION_UPDATE_IN_HEADER == 'true' && $version_from_ini != 'off' && ($version_check_sysinfo == true || $version_check_index == true) && $zv_db_patch_ok == true) || $version_check_requested == true) {
    $doVersionCheck = true;
    $versionServer = new VersionServer();
    $newinfo = $versionServer->getProjectVersion();
    $new_version = TEXT_VERSION_CHECK_CURRENT;

    if (empty($newinfo) || isset($newinfo['error'])) {
        $isCurrent = true;
        $versionCheckError = true;
    } else {
        $isCurrent = $versionServer->isProjectCurrent($newinfo);
    }

    $hasPatches = 0;
    if (!$isCurrent) {
        $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' :: ' . $newinfo['versionDetail'];
        $system_update_available = true;
    }
    if ($isCurrent) {
        $hasPatches = $versionServer->hasProjectPatches($newinfo);
    }
    if ($isCurrent && $hasPatches && $new_version == TEXT_VERSION_CHECK_CURRENT) {
        $new_version = '';
    }
    if ($isCurrent && $hasPatches != 2 && $hasPatches) {
        $new_version .= (($new_version != '') ? '<br>' : '') . '<span class="text-danger"><strong>' . TEXT_VERSION_CHECK_NEW_PATCH . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($newinfo['versionPatch1']) . '] :: ' . $newinfo['versionPatchDetail'] . '</strong></span>';
        $system_update_available = true;
    }

    if ($new_version != '' && $new_version != TEXT_VERSION_CHECK_CURRENT) {
        $new_version .= '<br><br><a href="' . $newinfo['versionDownloadURI'] . '" rel="noopener" target="_blank" class="btn btn-success btn-sm btn-block"><i class="fa fa-download"></i> ' . TEXT_VERSION_CHECK_DOWNLOAD . '</a>';
    } elseif ($new_version == TEXT_VERSION_CHECK_CURRENT) {
        $new_version = '<div class="text-center text-success"><i class="fa fa-check-circle fa-2x"></i><br>' . HEADER_TITLE_VERSION_SYSTEM_CHECK . '</div>';
    }
}

if (!$doVersionCheck || $versionCheckError) {
    $new_version = '';
    if ($versionCheckError) {
        $new_version = '<div class="text-danger">' . ERROR_CONTACTING_PROJECT_VERSION_SERVER . '</div><br>';
    }
    $url = zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('vcheck')), 'SSL');
    $url .= (strpos($url, '?') !== false ? '&amp;' : '?') . 'vcheck=yes';

    if ($zv_db_patch_ok == true || $version_check_sysinfo == true) {
        $new_version .= '<a href="' . $url . '" role="button" class="btn btn-primary btn-sm btn-block"><i class="fa fa-refresh"></i> ' . TEXT_VERSION_CHECK_BUTTON . '</a>';
    }
}

$current_ver_str = 'v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '');

// gv queue check
if (defined('MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN') && MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN == 'true') {
    $new_gv_queue = $db->Execute("SELECT * FROM " . TABLE_COUPON_GV_QUEUE . " WHERE release_flag='N'");
    $new_gv_queue_cnt = 0;
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
        [ 'a' => zen_catalog_href_link(FILENAME_DEFAULT), 'title' => HEADER_TITLE_ONLINE_CATALOG ],
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
                <a class="navbar-brand" href="<?= zen_href_link(FILENAME_DEFAULT) ?>">
                    <i class="fa fa-home"></i> <?= STORE_NAME ?> <small class="text-muted"><?= HEADER_TEXT_ADMIN ?></small>
                </a>
            </div>

            <div class="collapse navbar-collapse" id="top-bar-collapse">
                <?php
                echo zen_draw_form('orders', FILENAME_ORDERS, '', 'get', 'class="navbar-form navbar-left hidden-xs"', true);
                echo '<div class="form-group header-search">';
                echo zen_draw_input_field('oID', '', 'id="oID" class="form-control" placeholder="'.HEADER_TEXT_SEARCH_ORDERS.'"', '', '');
                echo zen_draw_hidden_field('action', 'edit');
                echo '</div>';
                echo '</form>';
                ?>

                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <a href="<?= zen_catalog_href_link(FILENAME_DEFAULT) ?>" target="_blank" title="<?= HEADER_TITLE_ONLINE_CATALOG ?>">
                            <i class="fa fa-external-link"></i> <span class="visible-xs-inline"> <?= HEADER_TITLE_ONLINE_CATALOG ?></span>
                        </a>
                    </li>

                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="<?= HEADER_TITLE_VERSION ?>">
                            <i class="fa fa-server <?= ($system_update_available ? 'text-danger' : '') ?>"></i> <span class="visible-xs-inline"> <?= HEADER_TITLE_VERSION ?></span>
                            <?php if ($system_update_available) { ?> <span class="badge-notify"></span> <?php } ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <div class="version-dropdown-content">
                                    <h5>
                                        <?= HEADER_TITLE_VERSION_SYSTEM_CHECK ?>
                                    </h5>
                                    <div>
                                        <?= $new_version ?>
                                    </div>
                                </div>
                                <div class="version-dropdown-footer">
                                    <?= TEXT_CURRENT_VER_IS . ' ' . $current_ver_str ?>
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
                                    <li><a href="<?= $item['a'] ?>" <?= (isset($item['params']) ? $item['params'] : '') ?>><i class="fa fa-plug"></i> <?= $item['title'] ?></a></li>
                                <?php } ?>
                            <?php } ?>

                            <li class="divider"></li>
                            <li class="header-info-menu">
                                <span class="info-label"><?= HEADER_TEXT_IP_ADDRESS ?></span>
                                <span class="info-val"><?= $admin_ip ?></span>

                                <span class="info-label"><?= HEADER_TEXT_HOSTNAME ?></span>
                                <span class="info-val"><?= $admin_host ?></span>

                                <span class="info-label"><?= HEADER_TEXT_TIMEZONE ?></span>
                                <span class="info-val"><?= $admin_tz . ($admin_locale ? ' (' . $admin_locale . ')' : '') ?></span>
                            </li>

                            <li class="divider"></li>
                            <li><a href="<?= zen_href_link(FILENAME_LOGOFF, '', 'NONSSL') ?>"><i class="fa fa-sign-out"></i> <?= HEADER_TITLE_LOGOFF ?></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div style="height: 50px;"></div>

<?php require(DIR_WS_INCLUDES . 'header_navigation.php') ?>

    <div class="container-fluid admin-alerts-wrapper noprint">
        <div class="visible-xs-block mb-3">
            <a class="btn btn-primary btn-block" role="button" href="<?= zen_href_link(FILENAME_ORDERS) ?>">
                <i class="fa fa-users"></i> <?= BOX_CUSTOMERS_ORDERS ?>
            </a>
        </div>

        <?php if (isset($_SESSION['reset_admin_activity_log']) && ($_SESSION['reset_admin_activity_log'] == true && (basename($PHP_SELF) == FILENAME_DEFAULT . '.php'))) { ?>
            <div class="alert alert-danger text-center mb-3">
                <strong><?= HEADER_TEXT_SECURITY_WARNING ?></strong><br>
                <?= RESET_ADMIN_ACTIVITY_LOG ?><br>
                <a class="btn btn-warning btn-xs mt-1" role="button" href="<?= zen_href_link(FILENAME_ADMIN_ACTIVITY) ?>">
                    <?= TEXT_BUTTON_RESET_ACTIVITY_LOG;?>
                </a>
            </div>
        <?php } ?>

        <?php if (!empty($new_gv_queue_cnt)) { ?>
            <div class="alert alert-info text-center mb-3">
                <strong><?= IMAGE_GIFT_QUEUE ?></strong><br>
                <?= sprintf(TEXT_SHOW_GV_QUEUE, $new_gv_queue_cnt) ?><br>
                <?= $goto_gv ?>
            </div>
        <?php } ?>
    </div>

<?php if(!empty($messageStack->output())) { ?>
    <div class="container-fluid mb-3">
        <?= $messageStack->output() ?>
    </div>
<?php } ?>
