<?php
/**
 * Featured
 *
 * @package page
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
if (MAX_DISPLAY_PRODUCTS_FEATURED_PRODUCTS > 0 )
{
  require_once(DIR_WS_MODULES . "listingboxes/class.zcListingBoxFeaturedDefault.php");
  $box = new zcListingBoxFeaturedDefault ();
  $box->init();
  $tplVars['listingBox'] = $box->getTemplateVariables ();
  require($template->get_template_dir('tpl_product_listing_standard.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_product_listing_standard.php');
}
