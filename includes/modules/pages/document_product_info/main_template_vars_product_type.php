<?php
/**
 * main_template_vars_product_type.php
 * This file contains all the logic to prepare $vars for use in the product-type-specific template
 * It pulls data from all the related tables which collectively store the info related only to this product type.
 *
 * @package productTypes
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: main_template_vars_product_type.php 2327 2005-11-10 00:38:31Z drbyte $
 */
/*
 * This file contains all the logic to prepare $vars for use in the product-type-specific template
 * It pulls data from all the related tables which collectively store the info related only to this product type.
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_PRODUCT_TYPE_VARS_START_DOCUMENT_PRODUCT_INFO');

/**
 * Retrieve relevant data from relational tables, for the current products_id:
 */


// Nothing special to do here for primary product_info type


/*
 * extract info from queries for use as template-variables:
 */

//nothing special to do here for this product type


  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_PRODUCT_TYPE_VARS_END_DOCUMENT_PRODUCT_INFO');
?>