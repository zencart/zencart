<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 19 Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (defined('STRICT_ERROR_REPORTING') && STRICT_ERROR_REPORTING == true) {
  $messageStack->add('STRICT ERROR REPORTING IS ON', 'error');
}
/*
 * pull in any necessary JS for the page
 * Left here for lagacy pages that do not use the new admin_html_head.php file
 */
require_once DIR_WS_INCLUDES . 'javascript_loader.php';

$version_check_requested = (isset($_GET['vcheck']) && $_GET['vcheck'] != '') ? true : false;

// Show Languages Dropdown for convenience only if main filename and directory exists
if ((basename($PHP_SELF) != FILENAME_DEFINE_LANGUAGE . '.php') and (basename($PHP_SELF) != FILENAME_PRODUCTS_OPTIONS_NAME . '.php') and empty($action)) {
    $languages_array = array();
    $languages = zen_get_languages();
    if (sizeof($languages) > 1) {
        //$languages_selected = $_GET['language'];
        $languages_selected = $_SESSION['language'];
        $missing_languages = '';
        $count = 0;
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $test_directory = DIR_WS_LANGUAGES . $languages[$i]['directory'];
            $test_file = DIR_WS_LANGUAGES . $languages[$i]['directory'] . '.php';
            if (file_exists($test_file) and file_exists($test_directory)) {
                $count++;
                $languages_array[] = array('id' => $languages[$i]['code'],
                                           'text' => $languages[$i]['name']);
                if ($languages[$i]['directory'] == $_SESSION['language']) {
                    $languages_selected = $languages[$i]['code'];
                }
            } else {
                $missing_languages .= ' ' . ucfirst($languages[$i]['directory']) . ' ' . $languages[$i]['name'];
            }
        }

// if languages in table do not match valid languages show error message
        if ($count != sizeof($languages)) {
            $messageStack->add('MISSING LANGUAGE FILES OR DIRECTORIES ...' . $missing_languages, 'caution');
        }
        $hide_languages = false;
    } else {
        $hide_languages = true;
    } // more than one language
} else {
    $hide_languages = true;
} // hide when other language dropdown is used


// display alerts/error messages, if any
if ($messageStack->size > 0) {
    ?>
    <div class="messageStack-header noprint">
        <?php
        echo $messageStack->output();
        ?>
    </div>
    <?php
}

// check version with zen-cart server
// ignore version-check if INI file setting has been set
$version_from_ini = '';
$version_ini_sysinfo = '';
$version_ini_index_sysinfo = '';
if (!isset($version_check_sysinfo)) $version_check_sysinfo = false;
if (!isset($version_check_index)) $version_check_index = false;

$file = DIR_FS_ADMIN . 'includes/local/skip_version_check.ini';
if (file_exists($file) && $lines = @file($file)) {
    foreach ($lines as $line) {
        if (substr(trim($line), 0, 14) == 'version_check=') $version_from_ini = substr(trim(strtolower(str_replace('version_check=', '', $line))), 0, 3);
        if (substr(trim($line), 0, 41) == 'display_update_link_only_on_sysinfo_page=') $version_ini_sysinfo = trim(strtolower(str_replace('display_update_link_only_on_sysinfo_page=', '', $line)));
        if (substr(trim($line), 0, 46) == 'display_update_link_on_index_and_sysinfo_page=') $version_ini_index_sysinfo = trim(strtolower(str_replace('display_update_link_only_on_sysinfo_page=', '', $line)));
    }
}

$doVersionCheck = false;
$versionCheckError = false;

// ignore version check if not enabled or if not on main page or sysinfo page
if ((SHOW_VERSION_UPDATE_IN_HEADER == 'true' && $version_from_ini != 'off' && ($version_check_sysinfo == true || $version_check_index == true) && $zv_db_patch_ok == true) || $version_check_requested == true) {
    $doVersionCheck = true;
    $versionServer = new VersionServer();
    $newinfo = $versionServer->getProjectVersion();
    $new_version = TEXT_VERSION_CHECK_CURRENT; //set to "current" by default
    if (isset($newinfo['error'])) {
        $isCurrent = true;
        $versionCheckError = true;
    } else {
        $isCurrent = $versionServer->isProjectCurrent($newinfo);
    }

    $hasPatches = 0;

    if (!$isCurrent) {
        $new_version = TEXT_VERSION_CHECK_NEW_VER . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' :: ' . $newinfo['versionDetail'];
    }
    if ($isCurrent) {
        $hasPatches = $versionServer->hasProjectPatches($newinfo);
    }

    if ($isCurrent && $hasPatches && $new_version == TEXT_VERSION_CHECK_CURRENT) {
        $new_version = '';
    }

    if ($isCurrent && $hasPatches != 2 && $hasPatches) {
        $new_version .= (($new_version != '') ? '<br />' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($newinfo['versionPatch1']) . '] :: ' . $newinfo['versionPatchDetail'] . '</span>';
    }

    if ($isCurrent && $hasPatches > 1) {
        $new_version .= (($new_version != '') ? '<br />' : '') . '<span class="alert">' . TEXT_VERSION_CHECK_NEW_PATCH . trim($newinfo['versionMajor']) . '.' . trim($newinfo['versionMinor']) . ' - ' . TEXT_VERSION_CHECK_PATCH . ': [' . trim($newinfo['versionPatch2']) . '] :: ' . $newinfo['versionPatchDetail'] . '</span>';
    }

    // display download link
    if ($new_version != '' && $new_version != TEXT_VERSION_CHECK_CURRENT) $new_version .= '<br /><a href="' . $newinfo['versionDownloadURI'] . '" rel="noopener" target="_blank"><input type="button" class="btn btn-success" value="' . TEXT_VERSION_CHECK_DOWNLOAD . '"/></a>';
}

if (!$doVersionCheck || $versionCheckError) {
    $new_version = '';
    if ($versionCheckError) {
        $new_version = ERROR_CONTACTING_PROJECT_VERSION_SERVER . '<br>';
    }
    // display the "check for updated version" button.  The button link should be the current page and all params
    $url = zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('vcheck'), 'SSL'));
    $url .= (strpos($url, '?') !== false ? '&amp;' : '?') . 'vcheck=yes';
    if ($zv_db_patch_ok == true || $version_check_sysinfo == true) $new_version .= '<a href="' . $url . '" role="button" class="btn btn-link">' . TEXT_VERSION_CHECK_BUTTON . '</a>';
}
/////////////////


// check GV release queue and alert store owner
if (defined('MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN') && MODULE_ORDER_TOTAL_GV_SHOW_QUEUE_IN_ADMIN == 'true') {
    $new_gv_queue = $db->Execute("SELECT * FROM " . TABLE_COUPON_GV_QUEUE . " WHERE release_flag='N'");
    $new_gv_queue_cnt = 0;
    if ($new_gv_queue->RecordCount() > 0) {
        $new_gv_queue_cnt = $new_gv_queue->RecordCount();
        $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE) . '">' . '<input type="button" class="btn btn-info" value="' . IMAGE_GIFT_QUEUE . '"/></a>';
    }
}
?>
<!-- All HEADER_ definitions in the columns below are defined in includes/languages/english.php //-->
  <div class="row">
    <div class="col-xs-8 col-sm-3" id="adminHeaderLogo">
        <?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT) . '</a>'; ?>
    </div>

    <div class="hidden-xs col-sm-3 col-sm-push-6 noprint adminHeaderAlerts">
        <?php if ($new_version) { ?>
            <?php echo $new_version; ?><br/>
            <?php echo '(' . TEXT_CURRENT_VER_IS . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . ')'; ?>
        <?php } ?>
    </div>

    <div class="hidden-sm hidden-md hidden-lg col-xs-4 noprint adminHeaderAlerts">
        <a class="btn btn-primary" role="button" href="<?php echo zen_href_link(FILENAME_ORDERS); ?>"><?php echo BOX_CUSTOMERS_ORDERS; ?></a>
    </div>

    <div class="clearfix visible-xs-block"></div>
    <div class="col-xs-6 col-sm-3 col-sm-pull-3 noprint adminHeaderAlerts">
        <?php
        if (isset($_SESSION['reset_admin_activity_log']) and ($_SESSION['reset_admin_activity_log'] == true and (basename($PHP_SELF) == FILENAME_DEFAULT . '.php'))) {
        ?>
        <a class="btn btn-warning" role="button" href="<?php echo zen_href_link(FILENAME_ADMIN_ACTIVITY); ?>"><?php echo TEXT_BUTTON_RESET_ACTIVITY_LOG;?></a><p class="hidden-xs"><br /><?php echo RESET_ADMIN_ACTIVITY_LOG; ?></p>
        <?php
        }
        ?>
    </div>

    <div class="col-xs-6 col-sm-3 col-sm-pull-3 noprint adminHeaderAlerts">
        <?php if (!empty($new_gv_queue_cnt)) echo $goto_gv . '<br />' . sprintf(TEXT_SHOW_GV_QUEUE, $new_gv_queue_cnt); ?>
    </div>

  </div>
  <div class="row headerBar">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
        <?php
        if (!$hide_languages) {
            echo zen_draw_form('languages', basename($PHP_SELF), '', 'get');
            echo DEFINE_LANGUAGE . '&nbsp;&nbsp;' . (sizeof($languages) > 1 ? zen_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') : '');
            echo zen_hide_session_id();
            echo zen_post_all_get_params(array('language'));
            echo '</form>';
        } else {
            echo '&nbsp;';
        }
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
        <?php
        echo((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? iconv('ISO-8859-1', 'UTF-8', strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())) : strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())); //windows does not "do" UTF-8...so a manual conversion is necessary
        echo '&nbsp;' . date("O", time()) . ' GMT';  // time zone
        echo '&nbsp;[' . $_SERVER['REMOTE_ADDR'] . ']'; // current admin user's IP address
        echo '<br />';
        echo gethostname(); 
        echo ' - ' . date_default_timezone_get(); //what is the PHP timezone set to?
        $loc = setlocale(LC_TIME, 0);
        if ($loc !== FALSE) echo ' - ' . $loc; //what is the locale in use?
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 noprint">
        <ul class="nav nav-pills upperMenu">
            <li><a href="<?php echo zen_href_link(FILENAME_DEFAULT, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_TOP; ?></a></li>
            <li><a href="<?php echo zen_catalog_href_link(FILENAME_DEFAULT); ?>" class="headerLink" rel="noopener" target="_blank"><?php echo HEADER_TITLE_ONLINE_CATALOG; ?></a></li>
            <li><a href="https://www.zen-cart.com/forum" class="headerLink" rel="noopener" target="_blank"><?php echo HEADER_TITLE_SUPPORT_SITE; ?></a></li>
            <li><a href="<?php echo zen_href_link(FILENAME_SERVER_INFO, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_VERSION; ?></a></li>
            <li><a href="<?php echo zen_href_link(FILENAME_ADMIN_ACCOUNT, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_ACCOUNT; ?></a></li>
            <li><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'NONSSL'); ?>" class="headerLink"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
        </ul>
    </div>
  </div>
<?php require(DIR_WS_INCLUDES . 'header_navigation.php'); ?>
