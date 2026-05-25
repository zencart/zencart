<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 Apr 10 Modified in v2.0.1 $
 */
/**
 * Template Settings
 *
 * You may define here any variables or settings that are specific to this template.
 * And then in your template, you can reference these variables as needed, independent of other templates.
 *
 * Any settings that you add to the $tpl_settings[] array can be retrieved via the $tplSetting object,
 * which also lets you lookup global constants defined in the Admin as a fallback in case that setting
 * isn't in the $tpl_settings array.
 *
 * NOTE: Wherever a template hard-codes a CONSTANT that's defined globally in the Admin, that will be used
 * directly whether or not there is an override set in this file.
 * To override any global setting you'll need to update your template to use the $tplSetting object instead.
 *
 * eg: SHOW_BANNERS_GROUP_SET1 could be replaced in your template with $tplSetting->SHOW_BANNERS_GROUP_SET1
 * and then first the settings below will be consulted, and if it's defined here it will be used, else it
 * will fallback to whatever the global setting is from the admin.
 *
 * NOTE: The ADMIN config area of your site will NOT be aware of any of these override settings as defined below.
 *
 * @var $tplSetting TemplateSettings
 */
/** TPL_SETTINGS ARRAY */
$tpl_settings['TEMPLATE_NAME'] = 'Responsive Classic';

//$tpl_settings['COLUMN_LEFT_STATUS'] = '1';
//$tpl_settings['COLUMN_RIGHT_STATUS'] = '1';
//$tpl_settings['SHOW_BANNERS_GROUP_SET1'] = 'group1';
//$tpl_settings['SHOW_BANNERS_GROUP_SET2'] = 'group2';





/**********************/

/** Other template-specific variables can be declared here */


$grid_product_cards_classes = 'row row-clmns-3';
$grid_product_classes_matrix = [
    // for responsive_classic the array index here is in 'pixels', because $center_column_width is in pixels. See tpl_main_page.php
    '480' => 'row row-clms-1 row-clms-sm-2 row-clms-md-3 row-clms-lg-4 row-clms-xl-6',
];

$grid_category_cards_classes = 'row row-clms-3';
$grid_category_classes_matrix = [
    '480' => 'row row-clms-1 row-clms-sm-2 row-clms-md-3 row-clms-lg-4 row-clms-xl-6',
];
