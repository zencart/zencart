<?php
/**
 * Page Template
 *
 * Loaded by main_page=index<br />
 * Displays product-listing when a particular category/subcategory is selected for browsing
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
//print_r($tplVars['listingBox']);
?>
<div class="centerColumn" id="indexProductList">
  <h1 id="productListHeading"><?php echo $current_categories_name; ?></h1>
<?php if (PRODUCT_LIST_CATEGORIES_IMAGE_STATUS == 'true') { ?>
<?php  if ($categories_image = zen_get_categories_image($current_category_id)) { ?>
<div id="categoryImgListing" class="categoryImg"><?php echo zen_image(DIR_WS_IMAGES . $categories_image, '', CATEGORY_ICON_IMAGE_WIDTH, CATEGORY_ICON_IMAGE_HEIGHT); ?></div>
<?php   } ?>
<?php } ?>

<?php if ($current_categories_description != '') { ?>
<div id="indexProductListCatDescription" class="content"><?php echo $current_categories_description;  ?></div>
<?php } ?>
<?php
if ($tplVars['listingBox']['showFiltersForm'])
{
  echo zen_draw_form ( 'filter', zen_href_link ( FILENAME_DEFAULT ), 'get' ) . '<label class="inputLabel">' . TEXT_SHOW . '</label>';
  echo zen_draw_hidden_field ( 'main_page', FILENAME_DEFAULT );
  echo zen_hide_session_id ();
  if (! $tplVars['getOptionsSet'])
  {
    echo zen_draw_hidden_field ( 'cPath', $cPath );
  } else
  {
    echo zen_draw_hidden_field ( $get_option_variable, $_GET[$tplVars['getOptionVariable']] );
  }
  if (isset ( $_GET['music_genre_id'] ) && $_GET['music_genre_id'] != '')
    echo zen_draw_hidden_field ( 'music_genre_id', $_GET['music_genre_id'] );
  if (isset ( $_GET['record_company_id'] ) && $_GET['record_company_id'] != '')
    echo zen_draw_hidden_field ( 'record_company_id', $_GET['record_company_id'] );
  if (isset ( $_GET['typefilter'] ) && $_GET['typefilter'] != '')
    echo zen_draw_hidden_field ( 'typefilter', $_GET['typefilter'] );
  if ($tplVars['getOptionVariable'] != 'manufacturers_id' && isset ( $_GET['manufacturers_id'] ) && $_GET['manufacturers_id'] > 0)
  {
    echo zen_draw_hidden_field ( 'manufacturers_id', $_GET['manufacturers_id'] );
  }
  echo zen_draw_hidden_field ( 'sort', $_GET['sort'] );
  if ($tplVars['listingBox']['filter']['doFilterList'])
  {
    echo zen_draw_pull_down_menu ( 'filter_id', $tplVars['listingBox']['filter']['filterOptions'], (isset ( $_GET['filter_id'] ) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"' );
  }
  require (DIR_WS_MODULES . zen_get_module_directory ( FILENAME_PRODUCT_LISTING_ALPHA_SORTER ));
  ?>
</form>
<?php } ?>
<br class="clearBoth" />
  <?php if ($tplVars['listingBox']['hasFormattedItems']) { ?>
<?php
/**
 * require the code for listing products
 */
require ($template->get_template_dir ( 'tpl_listingbox_tabular_default.php', DIR_WS_TEMPLATE, $current_page_base, 'listingboxes' ) . '/' . 'tpl_listingbox_tabular_default.php');
?>
<?php } else { ?>
<h2><?php echo TEXT_NO_PRODUCTS; ?></h2>
<?php } ?>
<?php foreach ($tplVars['listingBoxes'] as $tplVars['listingBox']) { ?>
<?php require($tplVars['listingBox']['template']); ?>
<?php } ?>
</div>
