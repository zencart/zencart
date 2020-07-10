<?php
/**
 * Page Template
 *
 * Displays page-not-found message and site-map (if configured)
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: DrByte  Sat Oct 17 21:58:04 2015 -0400 Modified in v1.5.5 $
 */
?>
<div class="centerColumn" id="pageNotFound">
<h1 id="pageNotFoundHeading"><?php echo HEADING_TITLE; ?></h1>

<?php if (DEFINE_PAGE_NOT_FOUND_STATUS == '1') { ?>
<div id="pageNotFoundMainContent" class="content">
<?php
/**
 * require the html_define for the page_not_found page
 */
  require($define_page); ?>
</div>
<?php } ?>

    <div id="siteMapList"><?php echo $zen_SiteMapTree->buildTree(); ?>
     <ul>
<?php if (SHOW_ACCOUNT_LINKS_ON_SITE_MAP=='Yes') { ?>
       <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . PAGE_ACCOUNT . '</a>'; ?>
       <ul>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . PAGE_ACCOUNT_EDIT . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . PAGE_ADDRESS_BOOK . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . PAGE_ACCOUNT_HISTORY . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . PAGE_ACCOUNT_NOTIFICATIONS . '</a>'; ?></li>
       </ul></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_SHOPPING_CART) . '">' . PAGE_SHOPPING_CART . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . PAGE_CHECKOUT_SHIPPING . '</a>'; ?></li>
<?php } //endif ?>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_ADVANCED_SEARCH) . '">' . PAGE_ADVANCED_SEARCH . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCTS_NEW) . '">' . PAGE_PRODUCTS_NEW . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_SPECIALS) . '">' . PAGE_SPECIALS . '</a>'; ?></li>
         <li><?php echo '<a href="' . zen_href_link(FILENAME_REVIEWS) . '">' . PAGE_REVIEWS . '</a>'; ?></li>
         <li><?php echo BOX_HEADING_INFORMATION; ?>
         <ul>
           <li><?php echo '<a href="' . zen_href_link(FILENAME_SHIPPING) . '">' . BOX_INFORMATION_SHIPPING . '</a>'; ?></li>
           <li><?php echo '<a href="' . zen_href_link(FILENAME_PRIVACY) . '">' . BOX_INFORMATION_PRIVACY . '</a>'; ?></li>
           <li><?php echo '<a href="' . zen_href_link(FILENAME_CONDITIONS) . '">' . BOX_INFORMATION_CONDITIONS . '</a>'; ?></li>
           <li><?php echo '<a href="' . zen_href_link(FILENAME_CONTACT_US, '', 'SSL') . '">' . BOX_INFORMATION_CONTACT . '</a>'; ?></li>
         </ul></li>
     </ul>
</div>

    <div class="buttonRow back"><?php echo zen_back_link() . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>
</div>
