<?php
/**
 * Specials
 *
 * @package page
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
if (MAX_DISPLAY_SPECIAL_PRODUCTS > 0 )
{
  require_once(DIR_WS_MODULES . "listingboxes/class.zcListingBoxSpecialsDefault.php");
  $box = new zcListingBoxSpecialsDefault ();
  $box->init();
  $listingBox = $box->getTemplateVariables ();
  $tplVars['listingBox'] = $listingBox;
  require($template->get_template_dir('tpl_product_listing_standard.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_product_listing_standard.php');
}
