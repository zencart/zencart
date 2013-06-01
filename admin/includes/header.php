<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: header.php 19529 2011-09-19 13:11:40Z wilt $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

// display alerts/error messages, if any
if ($messageStack->size > 0) {
  echo $messageStack->output();
}

?>
<!-- All HEADER_ definitions in the columns below are defined in includes/languages/english.php //-->
<table border="0" width="100%" cellspacing="0" cellpadding="0" class="header">
<?php
// special spacing for alt_nav.php
  if (basename($PHP_SELF) == 'alt_nav.php') {
?>
<tr><td>&nbsp;</td></tr>
<?php } // alt_nav spacing ?>
  <tr>
    <td align="left" valign="top" height="<?php echo HEADER_LOGO_HEIGHT; ?>" width="<?php echo HEADER_LOGO_WIDTH; ?>"><?php echo '<a href="' . zen_href_link(FILENAME_DEFAULT) . '">' . zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '</a>'; ?></td>
    <td colspan="2" align="left"><table width="100%"><tr>
    <td align="left" class="main" valign="top"><?php if ($new_gv_queue_cnt > 0) echo $goto_gv . '<br />' . sprintf(TEXT_SHOW_GV_QUEUE, $new_gv_queue_cnt); ?></td>
<?php
  if (isset($_SESSION['reset_admin_activity_log']) and ($_SESSION['reset_admin_activity_log'] == true and (basename($PHP_SELF) == FILENAME_DEFAULT . '.php'))) {
?>
    <td align="center" class="main" valign="top"><?php echo '<a href="' . zen_href_link(FILENAME_ADMIN_ACTIVITY) . '">' . zen_image_button('button_reset.gif', RESET_ADMIN_ACTIVITY_LOG) . '<br />' . RESET_ADMIN_ACTIVITY_LOG . '</a>'; ?></td>
<?php
  }
?>
<?php
  if ($new_version) {
?>
    <td align="right" class="main version-notify" valign="top"><?php echo $new_version; ?><br /><?php echo '(' . TEXT_CURRENT_VER_IS . ' v' . PROJECT_VERSION_MAJOR . '.' . PROJECT_VERSION_MINOR . (PROJECT_VERSION_PATCH1 != '' ? 'p' . PROJECT_VERSION_PATCH1 : '') . ')'; ?></td>
<?php
  }
?>
    </tr></table></td>
  </tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr class="headerBar">

    <td class="headerBarContent" align="left">
      <?php
      if (!$hide_languages) {
        echo zen_draw_form('languages', basename($PHP_SELF), '', 'get');
        echo DEFINE_LANGUAGE . '&nbsp;&nbsp;' . (sizeof($languages) > 1 ? zen_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') : '');
        echo zen_hide_session_id();
        echo '</form>';
      } else {
        echo '&nbsp;';
      }
    ?>
    </td>
    <td class="headerBarContent" align="center">
<?php
    echo ((strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? iconv('ISO-8859-1', 'UTF-8', strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())) : strftime(ADMIN_NAV_DATE_TIME_FORMAT, time())); //windows does not "do" UTF-8...so a manual conversion is necessary
    echo '&nbsp;' . date("O" , time()) . ' GMT';  // time zone
    echo '&nbsp;[' . $_SERVER['REMOTE_ADDR'] . ']'; // current admin user's IP address
    echo '<br />';
    echo @shell_exec('hostname'); //what server am I working on?
    echo ' - ' . getenv('TZ'); //what is the server timezone set to?
    echo ' - ' . setlocale(LC_TIME, 0); //what is the locale in use?
?></td>

    <td class="headerBarContent" align="right"><?php echo '
        <a href="' . zen_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '" class="headerLink">' . HEADER_TITLE_TOP . '</a>&nbsp;|&nbsp;
        <a href="' . zen_catalog_href_link() . '" class="headerLink" target="_blank">' . HEADER_TITLE_ONLINE_CATALOG . '</a>&nbsp;|&nbsp;
        <a href="http://www.zen-cart.com/" class="headerLink" target="_blank">' . HEADER_TITLE_SUPPORT_SITE . '</a>&nbsp;|&nbsp;
        <a href="' . zen_href_link(FILENAME_SERVER_INFO) . '" class="headerLink">' . HEADER_TITLE_VERSION . '</a>&nbsp;|&nbsp;
        <a href="' . zen_href_link(FILENAME_ADMIN_ACCOUNT) . '" class="headerLink">' . HEADER_TITLE_ACCOUNT . '</a>&nbsp;|&nbsp;
        <a href="' . zen_href_link(FILENAME_LOGOFF, '', 'SSL') . '" class="headerLink">' . HEADER_TITLE_LOGOFF . '</a>&nbsp;'; ?>
    </td>
  </tr>
</table>
<?php if (file_exists(DIR_WS_INCLUDES . 'keepalive_module.php')) require(DIR_WS_INCLUDES . 'keepalive_module.php'); ?>
<?php require(DIR_WS_INCLUDES . 'header_navigation.php'); ?>
