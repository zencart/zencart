<?php
/**
 * tpl_brands_default
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:   New in v1.5.8 $
 */
?>
<div id="indexBrandsList" class="centerColumn">

    <h1 id="indexBrandsList-pageHeading" class="pageHeading"><?php echo FEATURED_BRANDS; ?></h1>

    <div class="featuredBrands">
<?php
    $list_box_contents = $brands['featured'];
    $title = '';
    require($template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php');
?>
    </div>
    <br class="clearBoth">

    <div class="otherBrands">
        <h2><?php echo OTHER_BRANDS; ?></h2>
<?php
    $list_box_contents = $brands['other'];
    $title = '';
    require($template->get_template_dir('tpl_columnar_display.php', DIR_WS_TEMPLATE, $current_page_base, 'common') . '/tpl_columnar_display.php');
?>

    </div>

</div>
