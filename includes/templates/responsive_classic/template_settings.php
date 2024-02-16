<?php
/**
 * Template Settings
 *
 * You may define here any variables or settings that are specific to this template.
 * And then in your template, you can reference these variables as needed, independent of other templates.
 *
 * Any settings that you add to the $tpl_settings[] array can be retrieved via the tpl() helper function,
 * which also lets you lookup global constants defined in the Admin as a fallback in case that setting
 * isn't in the $tpl_settings array.
 *
 */
/** TPL_SETTINGS ARRAY */
$tpl_settings['TEMPLATE_NAME'] = 'Responsive Classic';

//$tpl_settings['SHOW_BANNERS_GROUP_SET1'] = 'group1';
//$tpl_settings['COLUMN_WIDTH_LEFT'] = '2';
//$tpl_settings['COLUMN_WIDTH_RIGHT'] = '2';
//$tpl_settings['COLUMN_LEFT_STATUS'] = '1';
//$tpl_settings['COLUMN_RIGHT_STATUS'] = '1';
//$tpl_settings['SHOW_BANNERS_GROUP_SET1'] = 'group1';
//$tpl_settings['SHOW_BANNERS_GROUP_SET2'] = 'group2';





/**********************/

/** Other template-specific variables can be declared here */


$grid_product_classes_matrix = [
    // for responsive_classic the array index here is in 'pixels', because $center_column_width is in pixels. See tpl_main_page.php
    '480' => 'row row-clms-1 row-clms-sm-2 row-clms-md-3 row-clms-lg-4 row-clms-xl-6',
];
$grid_category_classes_matrix = [
    '480' => 'row row-clms-1 row-clms-sm-2 row-clms-md-3 row-clms-lg-4 row-clms-xl-6',
];
