<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=search_result.
 * Displays results of search
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Dec 25 New in v1.5.8-alpha $
 */
?>
<div class="centerColumn" id="searchResultsDefault">

<h1 id="searchResultsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER == 'true') {
  $form = zen_draw_form('filter', zen_href_link(FILENAME_SEARCH_RESULT), 'get');
    //$form .= '<label class="inputLabel">' .TEXT_SHOW . '</label>';
?>
<?php echo $form; ?>
<?php
/* Redisplay all $_GET variables, except currency */
  echo zen_post_all_get_params('currency');

  require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));
?>
</form>
<?php
  }
?>
<?php
/**
 * Used to collate and display products from search results
 */
 require($template->get_template_dir('tpl_modules_product_listing.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_product_listing.php');
?>

<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_SEARCH, zen_get_all_get_params(array('sort', 'page', 'x', 'y')), 'NONSSL', true, false) . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</div>
