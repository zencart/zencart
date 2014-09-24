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
if (MAX_DISPLAY_PRODUCTS_NEW > 0 )
{
  $box = new \ZenCart\ListingBox\Build ($zcDiContainer, new \ZenCart\ListingBox\Box\AllDefault());
  $box->init();
  $tplVars['listingBox'] = $box->getTemplateVariables ();
  require($template->get_template_dir('tpl_product_listing_standard.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_product_listing_standard.php');
}
