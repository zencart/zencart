<?php
/**
 * Page Template
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Jan 31 Modified in v2.0.0-beta1 $
 */
?>
<div class="centerColumn" id="allProductsDefault">

<h1 id="allProductsDefaultHeading"><?php echo HEADING_TITLE; ?></h1>

<div id="filter-wrapper" class="group">
<?php
if (PRODUCT_LIST_ALPHA_SORTER === 'true') {
    $form = zen_draw_form('filter', zen_href_link(FILENAME_SEARCH_RESULT), 'get');
    $form .= '<label class="inputLabel">' . TEXT_SHOW . '</label>';
    echo $form;

    /* Redisplay all $_GET variables, except currency */
    echo zen_post_all_get_params('currency');

    require(DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER));

    echo '</form>';
}

/**
 * display the product sort dropdown
 */
require $template->get_template_dir('/tpl_modules_listing_display_order.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_listing_display_order.php';
?>
</div>

<?php
require $template->get_template_dir('tpl_modules_product_listing.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_product_listing.php';
?>

</div>
