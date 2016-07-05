<?php
/**
 * @package admin
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header.php  Modified in v1.6.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

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
?>
<!-- All HEADER_ definitions in the columns below are defined in includes/languages/english.php //-->
  <div class="row">
    <div class="col-xs-8 col-sm-3" id="adminHeaderLogo">
        <?php echo '<a href="' . zen_admin_href_link(FILENAME_DEFAULT) . '">' . zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT) . '</a>'; ?>
    </div>

    <div class="hidden-xs col-sm-3 col-sm-push-6 noprint adminHeaderAlerts">
        <?php if ($new_version) { ?>
            <?php echo $new_version; ?><br/>
            <?php echo '(' . TEXT_CURRENT_VER_IS . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . ')'; ?>
        <?php } ?>
    </div>

    <div class="hidden-sm hidden-md hidden-lg col-xs-4 noprint adminHeaderAlerts">
        <a href="<?php echo zen_admin_href_link(FILENAME_ORDERS); ?>"><input type="button" class="btn btn-primary" value="<?php echo BOX_CUSTOMERS_ORDERS; ?>"/></a>
    </div>

    <div class="clearfix visible-xs-block"></div>
    <div class="col-xs-6 col-sm-3 col-sm-pull-3 noprint adminHeaderAlerts">
<?php
  if (isset($_SESSION['reset_admin_activity_log']) and ($_SESSION['reset_admin_activity_log'] == true and (basename($PHP_SELF) == FILENAME_DEFAULT . '.php'))) {
?>
        <a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACTIVITY); ?>"><input type="button" class="btn btn-warning" value="<?php echo TEXT_BUTTON_RESET_ACTIVITY_LOG;?>"/></a><p class="hidden-xs"><br /><?php echo RESET_ADMIN_ACTIVITY_LOG; ?></p>
<?php
  }
?>
    </div>

    <div class="col-xs-6 col-sm-3 col-sm-pull-3 noprint adminHeaderAlerts">
        <?php if ($new_gv_queue_cnt > 0) echo $goto_gv . '<br />' . sprintf(TEXT_SHOW_GV_QUEUE, $new_gv_queue_cnt); ?>
    </div>

  </div>
  <div class="row headerBar">
    <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
      <?php
      if (!$hide_languages) {
        echo zen_draw_form('languages', $zcRequest->readGet('cmd'), '', 'get');
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
    echo ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? iconv('ISO-8859-1', 'UTF-8', strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())) : strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())); //windows does not "do" UTF-8...so a manual conversion is necessary
    echo '&nbsp;' . date("O" , time()) . ' GMT';  // time zone
    echo '&nbsp;[' . $_SERVER['REMOTE_ADDR'] . ']'; // current admin user's IP address
    echo '<br />';
    echo @gethostname(); //what server am I working on?
    echo ' - ' . date_default_timezone_get(); //what is the PHP timezone set to?
    $loc = setlocale(LC_TIME, 0);
    if ($loc !== FALSE) echo ' - ' . $loc; //what is the locale in use?
        ?>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 noprint">
        <ul class="nav nav-pills upperMenu">
            <li><a href="<?php echo zen_admin_href_link(FILENAME_DEFAULT); ?>" class="headerLink"><?php echo HEADER_TITLE_TOP; ?></a></li>
            <li><a href="<?php echo zen_catalog_href_link(FILENAME_DEFAULT); ?>" class="headerLink" target="_blank"><?php echo HEADER_TITLE_ONLINE_CATALOG; ?></a></li>
            <li><a href="https://www.zen-cart.com/" class="headerLink" target="_blank"><?php echo HEADER_TITLE_SUPPORT_SITE; ?></a></li>
            <li><a href="<?php echo zen_admin_href_link(FILENAME_SERVER_INFO); ?>" class="headerLink"><?php echo HEADER_TITLE_VERSION; ?></a></li>
            <li><a href="<?php echo zen_admin_href_link(FILENAME_ADMIN_ACCOUNT); ?>" class="headerLink"><?php echo HEADER_TITLE_ACCOUNT; ?></a></li>
            <li><a href="<?php echo zen_admin_href_link(FILENAME_LOGOFF); ?>" class="headerLink"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
        </ul>
    </div>
  </div>
<?php if (file_exists(DIR_WS_INCLUDES . 'keepalive_module.php')) require(DIR_WS_INCLUDES . 'keepalive_module.php'); ?>
<?php require(DIR_WS_INCLUDES . 'header_navigation.php'); ?>


