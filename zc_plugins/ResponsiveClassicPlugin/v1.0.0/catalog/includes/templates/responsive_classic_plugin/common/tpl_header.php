<?php
/**
 * Common Template - tpl_header.php
 *
 * this file can be copied to /templates/your_template_dir/pagename
 * example: to override the privacy page
 * make a directory /templates/my_template/privacy
 * copy /templates/templates_defaults/common/tpl_footer.php to /templates/my_template/privacy/tpl_header.php
 * to override the global settings and turn off the footer un-comment the following line:
 *
 * $flag_disable_header = true;
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Nick Fenwick 2023 Jul 04 Modified in v2.0.0-alpha1 $
 */
?>

<?php
  // Display all header alerts via messageStack:
  if ($messageStack->size('header') > 0) {
    echo $messageStack->output('header');
  }
  if (!empty($_GET['error_message'])) {
    echo zen_output_string_protected(urldecode($_GET['error_message']));
  }
  if (!empty($_GET['info_message'])) {
   echo zen_output_string_protected($_GET['info_message']);
}
// check whether to only display errors/alerts, or to also display the rest of the header
if (isset($flag_disable_header) && $flag_disable_header === true) {
  // do early-return from this template since $flag_disable_header is true
  return;
}
?>

<!--bof-header logo and navigation display-->

<div id="headerWrapper">

<!--bof navigation display-->
<div id="navMainWrapper" class="group onerow-fluid">
<?php
 if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) {
echo '<div class="header Fixed"><a href="#menu" title="Menu"><i class="fa-solid fa-bars"></i></a></div>';
 } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ){
echo '<div class="header Fixed"><a href="#menu" title="Menu"><i class="fa-solid fa-bars"></i></a></div>';
} else {
//
}
?>

<?php if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) { ?>

<div id="navMain">
  <ul>
    <li><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'; ?><i class="fa-solid fa-house" title="Home"></i></a></li>
    <li><a href="#top"><i class="fa-solid fa-circle-arrow-up" title="Back to Top"></i></a></li>
<?php
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><i class="fa-solid fa-arrow-right-from-bracket" title="Log Off"></i></a></li>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><i class="fa-solid fa-user" title="My Account"></i></a></li>
<?php } else { ?>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><i class="fa-solid fa-user" title="My Account"></i></a></li>
<?php } ?>
<?php
      } else {
        if (STORE_STATUS == '0') {
?>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><i class="fa-solid fa-arrow-right-to-bracket" title="Log In"></i></a></li>
<?php } else { ?>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><i class="fa-solid fa-arrow-right-to-bracket" title="Log In"></i></a></li>
<?php } ?>
<?php
  }
}
?>

<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a class="navCartContentsIndicator" href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><i class="fa-solid fa-cart-shopping" title="Shopping Cart"></i></a></li>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><i class="fa-solid fa-square-check" title="Checkout"></i></a></li>
<?php }?>
  </ul>
<div id="navMainSearch" class="forward">
  <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
</div>
</div>
</div>

<!--eof navigation display-->

<?php  } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ){ ?>

<div id="navMain">
    <ul>
<li class="hide"><a href="#top"><i class="fa-solid fa-circle-arrow-up" title="Back to Top"></i></a></li>
    <li><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'; ?><?php echo HEADER_TITLE_CATALOG; ?></a></li>
<?php
        if (zen_is_logged_in() && !zen_in_guest_checkout()) {
?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
<li><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a></li>
	    <?php } else { ?>
<li class="last"><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a></li>

      <?php } ?>
<?php
      } else {
        if (STORE_STATUS == '0') {
?>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a></li>
	    <?php } else { ?>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a></li>

	    <?php } ?>
<?php } } ?>

<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a class="navCartContentsIndicator" href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><i class="fa-solid fa-cart-shopping" title="Shopping Cart"></i>
     <?php
        echo HEADER_TITLE_CART_CONTENTS;
        // Alternatively, if you want to display cart quantity and value, use the following line instead of the one above. Adapt for multiple languages if relevant.
        // echo $_SESSION['cart']->count_contents().' item(s) '. $currencies->format($_SESSION['cart']->show_total());
     ?>
   </a>
    </li>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo HEADER_TITLE_CHECKOUT; ?></a></li>
<?php }?>
</ul>
<div id="navMainSearch" class="forward">
   <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
</div>
</div>
</div>
<!--eof navigation display-->

<?php  } else { ?>
<div id="navMain">
  <ul class="back">
    <li><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'; ?><?php echo HEADER_TITLE_CATALOG; ?></a></li>
<?php
    if (zen_is_logged_in() && !zen_in_guest_checkout()) {
?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a></li>
<?php } else { ?>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a></li>
<?php } ?>
<?php
      } else {
        if (STORE_STATUS == '0') {
?>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a></li>
	    <?php } else { ?>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a></li>
<?php } ?>
<?php
  }
 }
?>

<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li>
      <a class="navCartContentsIndicator" href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><i class="fa-solid fa-cart-shopping" title="Shopping Cart"></i>
     <?php
        echo HEADER_TITLE_CART_CONTENTS;
        // Alternatively, if you want to display cart quantity and value, use the following line instead of the one above. Adapt for multiple languages if relevant.
        // echo $_SESSION['cart']->count_contents().' item(s) '. $currencies->format($_SESSION['cart']->show_total());
     ?>
   </a>
   </li>
    <li class="last"><a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo HEADER_TITLE_CHECKOUT; ?></a></li>
<?php }?>
  </ul>
<div id="navMainSearch" class="forward">
     <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
</div>
</div>
</div>
<!--eof navigation display-->

<?php  } ?>

<!--bof branding display-->
<div id="logoWrapper" class="group onerow-fluid">
    <div id="logo"><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">' . zen_image($template->get_template_dir(HEADER_LOGO_IMAGE, DIR_WS_TEMPLATE, $current_page_base,'images'). '/' . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT) . '</a>'; ?>
<?php if (HEADER_SALES_TEXT != '' || (SHOW_BANNERS_GROUP_SET2 != '' && $banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET2))) { ?>
    <div id="taglineWrapper">
<?php
  if (HEADER_SALES_TEXT != '') {
?>
      <div id="tagline"><?php echo HEADER_SALES_TEXT;?></div>
<?php
  }
?>
<?php
  if (SHOW_BANNERS_GROUP_SET2 != '' && $banner = zen_banner_exists('dynamic', SHOW_BANNERS_GROUP_SET2)) {
    if ($banner->RecordCount() > 0) {
?>
  <div id="bannerTwo" class="banners"><?php echo zen_display_banner('static', $banner);?></div>
<?php
    }
  }
?>
  </div>
<?php } // no HEADER_SALES_TEXT or SHOW_BANNERS_GROUP_SET2 ?>
  </div>
</div>

<!--eof branding display-->
<!--eof header logo and navigation display-->

<?php if ( $detect->isMobile() && !$detect->isTablet() || $_SESSION['layoutType'] == 'mobile' ) { ?>
  <div id="navMainSearch1" class="forward">
     <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
  </div>
<?php  } else if ( $detect->isTablet() || $_SESSION['layoutType'] == 'tablet' ) { ?>
  <div id="navMainSearch1" class="forward">
     <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
  </div>
<?php  } else if ( $_SESSION['layoutType'] == 'full' ) {
  } else {
//
  }
?>

<!--bof optional categories tabs navigation display-->
<?php require($template->get_template_dir('tpl_modules_categories_tabs.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_categories_tabs.php'); ?>
<!--eof optional categories tabs navigation display-->

<!--bof header ezpage links-->
<?php if (EZPAGES_STATUS_HEADER == '1' or (EZPAGES_STATUS_HEADER == '2' && zen_is_whitelisted_admin_ip())) { ?>
<?php   require($template->get_template_dir('tpl_ezpages_bar_header.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_ezpages_bar_header.php'); ?>
<?php } ?>
<!--eof header ezpage links-->
</div>
