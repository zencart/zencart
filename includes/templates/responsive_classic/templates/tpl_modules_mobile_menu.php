<?php
/**
 * Module Template for responsive mobile support
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 May 16 Modified in v1.5.7 $
 */
?>

<nav id="menu">
  <ul>
    <li><?php echo '<a href="' . HTTP_SERVER . DIR_WS_CATALOG . '">'; ?><?php echo HEADER_TITLE_CATALOG; ?></a></li>
<?php  if (DEFINE_CONTACT_US_STATUS <= 1) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_CONTACT_US, '', 'SSL'); ?>"><?php echo BOX_INFORMATION_CONTACT; ?></a></li>
<?php  } ?>

<?php if (zen_is_logged_in()) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGOFF, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGOFF; ?></a></li>
    <li><a href="<?php echo zen_href_link(FILENAME_ACCOUNT, '', 'SSL'); ?>"><?php echo HEADER_TITLE_MY_ACCOUNT; ?></a></li>
<?php
  } elseif (STORE_STATUS == '0') {
?>
    <li><a href="<?php echo zen_href_link(FILENAME_LOGIN, '', 'SSL'); ?>"><?php echo HEADER_TITLE_LOGIN; ?></a></li>
<?php } ?>
<?php if ($_SESSION['cart']->count_contents() != 0) { ?>
    <li><a href="<?php echo zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL'); ?>"><?php echo HEADER_TITLE_CART_CONTENTS; ?></a></li>
    <li><a href="<?php echo zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'); ?>"><?php echo HEADER_TITLE_CHECKOUT; ?></a></li>
<?php }?>

    <li><span><?php echo BOX_HEADING_CATEGORIES; ?></span>
<?php
  // load the UL-generator class and produce the menu list dynamically from there
   require_once (DIR_WS_CLASSES . 'categories_ul_generator.php');
$zen_CategoriesUL = new zen_categories_ul_generator;
$menulist = $zen_CategoriesUL->buildTree(true);
$menulist = str_replace('"level4"','"level5"',$menulist);
$menulist = str_replace('"level3"','"level4"',$menulist);
$menulist = str_replace('"level2"','"level3"',$menulist);
$menulist = str_replace('"level1"','"level2"',$menulist);
$menulist = str_replace('<li>','<li>',$menulist);
$menulist = str_replace("</li>\n</ul>\n</li>\n</ul>\n","</li>\n</ul>\n",$menulist);
echo $menulist;
?>       
    </li>

<?php
  if (SHOW_CATEGORIES_BOX_SPECIALS == 'true') {
   $show_this = $db->Execute("select s.products_id from " . TABLE_SPECIALS . " s where s.status= 1 limit 1");
   if ($show_this->RecordCount() > 0) { ?>
    <li><a class="category-links" href="<?php echo zen_href_link(FILENAME_SPECIALS); ?>"><?php echo CATEGORIES_BOX_HEADING_SPECIALS; ?></a></li>
<?php  
    }
  }
?>

<?php if (SHOW_CATEGORIES_BOX_PRODUCTS_NEW == 'true') {
      // display limits
//    $display_limit = zen_get_products_new_timelimit();
      $display_limit = zen_get_new_date_range();

      $show_this = $db->Execute("select p.products_id
                                 from " . TABLE_PRODUCTS . " p
                                 where p.products_status = 1 " . $display_limit . " limit 1");
      if ($show_this->RecordCount() > 0) { 
?>
    <li><a class="category-links" href="<?php echo zen_href_link(FILENAME_PRODUCTS_NEW); ?>"><?php echo CATEGORIES_BOX_HEADING_WHATS_NEW; ?></a></li>
<?php 
    }
  } 
?>
<?php if (SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS == 'true') {
       $show_this = $db->Execute("select products_id from " . TABLE_FEATURED . " where status= 1 limit 1");
       if ($show_this->RecordCount() > 0) { 
?>
    <li><a class="category-links" href="<?php echo zen_href_link(FILENAME_FEATURED_PRODUCTS); ?>"><?php echo CATEGORIES_BOX_HEADING_FEATURED_PRODUCTS; ?></a></li>
<?php
    }
  }
?>
<?php if (SHOW_CATEGORIES_BOX_PRODUCTS_ALL == 'true') { ?>
    <li><a class="category-links" href="<?php echo zen_href_link(FILENAME_PRODUCTS_ALL); ?>"><?php echo CATEGORIES_BOX_HEADING_PRODUCTS_ALL; ?></a></li>
<?php } ?>

    <li><span><?php echo BOX_HEADING_INFORMATION; ?></span>
      <ul>
<?php if (DEFINE_SHIPPINGINFO_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_SHIPPING); ?>"><?php echo BOX_INFORMATION_SHIPPING; ?></a></li>
<?php } ?>
<?php if (DEFINE_PRIVACY_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_PRIVACY); ?>"><?php echo BOX_INFORMATION_PRIVACY; ?></a></li>
<?php } ?>
<?php if (DEFINE_CONDITIONS_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_CONDITIONS); ?>"><?php echo BOX_INFORMATION_CONDITIONS; ?></a></li>
<?php } ?>
<?php if (!empty($external_bb_url) && !empty($external_bb_text)) { // forum/bb link ?>
        <li><a href="<?php echo $external_bb_url; ?>" rel="noopener" target="_blank"><?php echo $external_bb_text; ?></a></li>
<?php } ?>
<?php if (DEFINE_SITE_MAP_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_SITE_MAP); ?>"><?php echo BOX_INFORMATION_SITE_MAP; ?></a></li>
<?php } ?>
<?php if (defined('MODULE_ORDER_TOTAL_GV_STATUS') && MODULE_ORDER_TOTAL_GV_STATUS == 'true') { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_GV_FAQ); ?>"><?php echo BOX_INFORMATION_GV; ?></a></li>
<?php } ?>
<?php if (DEFINE_DISCOUNT_COUPON_STATUS <= 1 && defined('MODULE_ORDER_TOTAL_COUPON_STATUS') && MODULE_ORDER_TOTAL_COUPON_STATUS == 'true') { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_DISCOUNT_COUPON); ?>"><?php echo BOX_INFORMATION_DISCOUNT_COUPONS; ?></a></li>
<?php } ?>
<?php if (SHOW_NEWSLETTER_UNSUBSCRIBE_LINK == 'true') { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_UNSUBSCRIBE); ?>"><?php echo BOX_INFORMATION_UNSUBSCRIBE; ?></a></li>
<?php } ?>
<?php if (DEFINE_PAGE_2_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_PAGE_2); ?>"><?php echo BOX_INFORMATION_PAGE_2; ?></a></li>
<?php } ?>
<?php if (DEFINE_PAGE_3_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_PAGE_3); ?>"><?php echo BOX_INFORMATION_PAGE_3; ?></a></li>
<?php } ?>
<?php if (DEFINE_PAGE_4_STATUS <= 1) { ?>
        <li><a href="<?php echo zen_href_link(FILENAME_PAGE_4); ?>"><?php echo BOX_INFORMATION_PAGE_4; ?></a></li>
<?php } ?>
      </ul>
    </li>

    <li><span><?php echo BOX_HEADING_EZPAGES; ?></span>
      <ul>
<?php
  include(DIR_WS_MODULES . zen_get_module_directory('ezpages_bar_header.php'));
  if (!empty($var_linksList)) {
    for ($i=1, $n=sizeof($var_linksList); $i<=$n; $i++) {
      echo '<li><a href="' . $var_linksList[$i]['link'] . '">' . $var_linksList[$i]['name'] . '</a></li>' . "\n";
    }
  }
?>
      </ul>
    </li>


    <li id="menu-search">
      <?php require(DIR_WS_MODULES . zen_get_module_sidebox_directory('search_header.php')); ?>
    </li>

  </ul>
</nav>

<script src="<?php echo $template->get_template_dir('jquery.mmenu.min.all.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/jquery.mmenu.min.all.js' ?>" type="text/javascript"></script>
<script src="<?php echo $template->get_template_dir('jquery.mmenu.fixedelements.min.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/jquery.mmenu.fixedelements.min.js' ?>" type="text/javascript"></script>
<script type="text/javascript">
  $(function() {
    $("#menu")
      .mmenu({
        slidingSubmenus : false,
        extensions : [ "theme-dark", "effect-listitems-slide" ],
        iconPanels: {
          add : true,
          visible: 1
        },
        navbar: {
          add : false
        },
        counters: true
      }).on('click', 'a[href^="#/"]', function() {
        alert("Thank you for clicking, but that's a demo link.");
        return false;
      });
  });
</script>
