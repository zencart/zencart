<?php
/**
 * Common Template - tpl_main_page.php
 *
 * Governs the overall layout of an entire page<br />
 * Normally consisting of a header, left side column. center column. right side column and footer<br />
 * For customizing, this file can be copied to /templates/your_template_dir/pagename<br />
 * example: to override the privacy page<br />
 * - make a directory /templates/my_template/privacy<br />
 * - copy /templates/templates_defaults/common/tpl_main_page.php to /templates/my_template/privacy/tpl_main_page.php<br />
 * <br />
 * to override the global settings and turn off columns you can either update the main_template_vars.php in the common folder
 * or add the relevant $flag_disable_xxxx variable in this file, below.
 * A more universal solution would be a strategic use of an observer class to alter the relevant variables before they're used.
 *
 * @package templateSystem
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_main_page.php 7085 2007-09-22 04:56:31Z ajeh $
 */

  // Notifier hook to allow for dynamic changes to template operation
  $zco_notifier->notify('NOTIFY_TPL_MAIN_PAGE_BEFORE_BODY', $body_id, $template_dir);
/** bof DESIGNER TESTING ONLY: */
// $messageStack->add('header', 'this is a sample error message', 'error');
// $messageStack->add('header', 'this is a sample caution message', 'caution');
// $messageStack->add('header', 'this is a sample success message', 'success');
// $messageStack->add('main', 'this is a sample error message', 'error');
// $messageStack->add('main', 'this is a sample caution message', 'caution');
// $messageStack->add('main', 'this is a sample success message', 'success');
/** eof DESIGNER TESTING ONLY */

?>
<body id="<?php echo $body_id . 'Body'; ?>"<?php if ($bodyClasses !='') echo ' class="' . trim($bodyClasses) . '"'; ?>>

<?php
 /**
  * prepares and displays navigation output
  */
if (!$flag_disable_nav_menu) {
  require($template->get_template_dir($header_nav_menu_template,DIR_WS_TEMPLATE, $current_page_base,'common'). '/' . $header_nav_menu_template);
}
?>

<?php
  if ($banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET1)) {
?>
<div id="bannerOne" class="banners"><?php echo zen_display_banner('static', $banner); ?></div>
<?php
  }
?>

<div id="mainWrapper">
<?php
 /**
  * prepares and displays header output
  *
  */
  require($template->get_template_dir($header_template,DIR_WS_TEMPLATE, $current_page_base,'common'). '/' . $header_template);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="contentMainWrapper">
  <tr>
<?php
if (!isset($flag_disable_left) || !$flag_disable_left) {
?>
 <td id="navColumnOne" class="columnLeft" style="width: <?php echo COLUMN_WIDTH_LEFT; ?>">
<?php
 /**
  * prepares and displays left column sideboxes
  *
  */
?>
<div id="navColumnOneWrapper" style="width: <?php echo BOX_WIDTH_LEFT; ?>"><?php require(DIR_WS_MODULES . zen_get_module_directory('column_left.php')); ?></div></td>
<?php
}
?>
    <td valign="top">
<!-- bof  breadcrumb -->
<?php if (DEFINE_BREADCRUMB_STATUS == '1' || (DEFINE_BREADCRUMB_STATUS == '2' && !$this_is_home_page) ) { ?>
    <div id="navBreadCrumb" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><?php echo $breadcrumb->trail(BREAD_CRUMBS_SEPARATOR); ?></div>
<?php } ?>
<!-- eof breadcrumb -->

<?php
  if ($banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET3)) {
?>
<div id="bannerThree" class="banners"><?php echo zen_display_banner('static', $banner); ?></div>
<?php
  }
?>

<!-- bof messagestack alerts -->
<?php if ($messageStack->size('upload') > 0) echo $messageStack->output('upload'); ?>
<?php if ($messageStack->size('main') > 0) echo $messageStack->output('main'); ?>
<!-- eof messagestack alerts -->

<?php
 /**
  * prepares and displays center column
  */
 require($body_code); ?>

<?php
  if ($banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET4)) {
?>
<div id="bannerFour" class="banners"><?php echo zen_display_banner('static', $banner); ?></div>
<?php
  }
?></td>

<?php
if (!isset($flag_disable_right) || !$flag_disable_right) {
?>
<td id="navColumnTwo" class="columnRight" style="width: <?php echo COLUMN_WIDTH_RIGHT; ?>">
<?php
 /**
  * prepares and displays right column sideboxes
  *
  */
?>
<div id="navColumnTwoWrapper" style="width: <?php echo BOX_WIDTH_RIGHT; ?>"><?php require(DIR_WS_MODULES . zen_get_module_directory('column_right.php')); ?></div></td>
<?php
}
?>
  </tr>
</table>

<?php
 /**
  * prepares and displays footer output
  *
  */
  require($template->get_template_dir($footer_template,DIR_WS_TEMPLATE, $current_page_base,'common'). '/' . $footer_template);
?>

</div>
<!--bof- parse time display -->
<?php
  if (DISPLAY_PAGE_PARSE_TIME == 'true') {
?>
<div class="smallText center">Parse Time: <?php echo $parse_time; ?> - Number of Queries: <?php echo $db->queryCount(); ?> - Query Time: <?php echo $db->queryTime(); ?></div>
<?php
  }
?>
<!--eof- parse time display -->
<!--bof- banner #6 display -->
<?php
  if ($banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET6)) {
?>
<div id="bannerSix" class="banners"><?php echo zen_display_banner('static', $banner); ?></div>
<?php
  }
?>
<!--eof- banner #6 display -->





<?php
/*************** JAVASCRIPT OUTPUT before </body> close ************************/
  // run dynamically-generated jscript_XXXXX.php files
  foreach ($jscripts as $val) {
    require($val);
    echo "\n";
  }

  // output src links to .js scripts
  echo $template_js_output_bottom;
?>
</body>
</html>
