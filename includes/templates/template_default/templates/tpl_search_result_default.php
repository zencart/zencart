<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=search_result.
 * Displays results of search
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2025 Oct 03 Modified in v2.2.0 $
 */
?>
<div class="centerColumn" id="searchResultsDefault">
    <h1 id="searchResultsDefaultHeading"><?= HEADING_TITLE ?></h1>

<?php
if ($messageStack->size('search') > 0) {
    echo $messageStack->output('search');
}
?>
    <div id="filter-wrapper" class="group">
<?php
if ($do_filter_list || PRODUCT_LIST_ALPHA_SORTER === 'true') {
    $form = zen_draw_form('filter', zen_href_link(FILENAME_SEARCH_RESULT), 'get');
    $form .= '<label class="inputLabel">' . TEXT_SHOW . '</label>';
    echo $form;
    
    // -----
    // Don't include 'disp_order' and 'sort' if defaulted.
    //
    if (empty($_GET['disp_order']) || $_GET['disp_order'] === '8') {
        unset($_GET['disp_order']);
    }
    if (!empty($_GET['sort']) && $_GET['sort'] === '20a') {
        unset($_GET['sort']);
    }

    /* Redisplay all $_GET variables, except currency and page */
    echo zen_post_all_get_params(['currency', 'page']);
    require DIR_WS_MODULES . zen_get_module_directory(FILENAME_PRODUCT_LISTING_ALPHA_SORTER);

    echo '</form>';
}

/**
* display the product display-order dropdown
*/
require $template->get_template_dir('/tpl_modules_listing_display_order.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_listing_display_order.php';
?>
    </div>
<?php
/**
 * Used to collate and display products from search results
 */
require $template->get_template_dir('tpl_modules_product_listing.php', DIR_WS_TEMPLATE, $current_page_base, 'templates'). '/tpl_modules_product_listing.php';
?>

    <div class="buttonRow back">
        <a href="<?= zen_href_link(FILENAME_SEARCH, zen_get_all_get_params(['sort', 'page', 'x', 'y']), 'NONSSL', true, false) ?>">
            <?= zen_image_button(BUTTON_IMAGE_BACK, BUTTON_BACK_ALT) ?>
        </a>
    </div>

</div>
