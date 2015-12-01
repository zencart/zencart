<?php
/**
 * Module Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2014 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  $
 */
?>
<?php
// NOTE: to remove a sort order option add an HTML comment around the option to be removed
?>

<div id="sorter">
<label for="disp-order-sorter"><?php echo TEXT_INFO_SORT_BY; ?></label>
<?php
  echo zen_draw_form('sorter_form', zen_href_link($_GET['main_page']), 'get');
  echo zen_draw_hidden_field('main_page', $_GET['main_page']);
//  echo zen_draw_hidden_field('disp_order', $_GET['disp_order']);
  echo zen_hide_session_id();
?>
    <select name="disp_order" onchange="this.form.submit();" id="disp-order-sorter">
<?php if ($tplVars['listingBox']['filter']['displayOrder'] != $tplVars['listingBox']['filter']['displayOrderDefault']) { ?>
    <option value="<?php echo $tplVars['listingBox']['filter']['displayOrderDefault']; ?>" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == $tplVars['listingBox']['filter']['displayOrderDefault'] ? 'selected="selected"' : ''); ?>><?php echo PULL_DOWN_ALL_RESET; ?></option>
<?php } // reset to store default ?>
    <option value="1" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '1' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_NAME; ?></option>
    <option value="2" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '2' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_NAME_DESC; ?></option>
    <option value="3" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '3' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_PRICE; ?></option>
    <option value="4" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '4' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_PRICE_DESC; ?></option>
    <option value="5" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '5' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_MODEL; ?></option>
    <option value="6" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '6' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_DATE_DESC; ?></option>
    <option value="7" <?php echo ($tplVars['listingBox']['filter']['displayOrder'] == '7' ? 'selected="selected"' : ''); ?>><?php echo TEXT_INFO_SORT_BY_PRODUCTS_DATE; ?></option>
    </select></form></div>