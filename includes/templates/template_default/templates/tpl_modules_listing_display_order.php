<?php
/**
 * Module Template
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Mar 09 Modified in v2.0.0-rc2 $
 */
$disp_order = (int)($disp_order ?? 0);
$disp_order_default = (int)($disp_order_default ?? 0);
if ($disp_order === 0) {
    $disp_order = $disp_order_default;
}

echo zen_draw_form('sorter_form', zen_href_link($_GET['main_page']), 'get');
foreach($_GET as $key => $value) {
    if (in_array($key, ['main_page', 'cPath', 'manufacturers_id', 'filter_id', 'alpha_filter_id', 'music_genre_id', 'record_company_id'])) {
        echo zen_draw_hidden_field($key, $value);
    }
    
    if ($current_page_base === FILENAME_SEARCH_RESULT && in_array($key, ['keyword', 'search_in_description', 'categories_id', 'inc_subcat', 'pfrom', 'pto', 'dfrom', 'dto'])) {
        echo zen_draw_hidden_field($key, $value);
    }
}
echo zen_hide_session_id();
?>
<label for="disp-order-sorter" class="inputLabel"><?= TEXT_INFO_SORT_BY; ?></label>
<select name="disp_order" onchange="this.form.submit();" aria-label="<?= TEXT_INFO_SORT_BY;?>" id="disp-order-sorter">
<?php
if (false && $disp_order > 0) { // reset to store default
?>
    <option value="<?= $disp_order_default;?>" <?= ($disp_order === $disp_order_default ? 'selected="selected"' : '') ?>><?= PULL_DOWN_ALL_RESET ?></option>
<?php
}

// NOTE: to remove a sort order option add an HTML comment around the option to be removed
?>
    <option value="8" <?= ($disp_order === 8 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_RECOMMENDED;?></option>
    <option value="1" <?= ($disp_order === 1 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_NAME ?></option>
    <option value="2" <?= ($disp_order === 2 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_NAME_DESC ?></option>
    <option value="3" <?= ($disp_order === 3 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_PRICE ?></option>
    <option value="4" <?= ($disp_order === 4 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_PRICE_DESC ?></option>
    <option value="5" <?= ($disp_order === 5 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_MODEL ?></option>
    <option value="6" <?= ($disp_order === 6 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_DATE_DESC ?></option>
    <option value="7" <?= ($disp_order === 7 ? 'selected="selected"' : ''); ?>><?= TEXT_INFO_SORT_BY_PRODUCTS_DATE ?></option>
</select>
<?= '</form>';
