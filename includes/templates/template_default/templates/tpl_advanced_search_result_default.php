<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=advanced_search_result.<br />
 * Displays results of advanced search
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
?>
<div class="centerColumn" id="advSearchResultsDefault">

<h1 id="advSearchResultsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<?php
  if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER == 'true') {
//  $form = zen_draw_form('filter', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT), 'get') . '<label class="inputLabel">' .TEXT_SHOW . '</label>';
  $form = zen_draw_form('filter', zen_href_link(FILENAME_ADVANCED_SEARCH_RESULT), 'get');
?>
<?php echo $form; ?>
<?php
  echo zen_hide_session_id();

/* Re-Get all GET'ed variables */
  echo zen_post_all_get_params('currency');

  require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));
?>
</form>
<?php
  }
?>
<?php require($template->get_template_dir($tplVars['listingBox']['formatter']['template'], DIR_WS_TEMPLATE, $current_page_base, 'listingboxes') . '/' . $tplVars['listingBox']['formatter']['template']); ?>

<div class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_ADVANCED_SEARCH, zen_get_all_get_params(array('sort', 'page', 'x', 'y')), 'NONSSL', true, false) . '">' . zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) . '</a>'; ?></div>

</div>
