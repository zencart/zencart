<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 15 Modified in v1.5.7 $
 */

// to disable this module for everyone, uncomment the following "return" statement so the rest of this file is ignored
// return;

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_CUSTOMERS, false, true, 1800);
$customers = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_PRODUCTS . " WHERE products_status = 1", false, true, 1800);
$products = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_PRODUCTS . " WHERE products_status = 0", false, true, 1800);
$products_off = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_REVIEWS);
$reviews = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_REVIEWS . " WHERE status = 0", false, true, 1800);
$reviews_pending = $result->fields['count'];

$result = $db->Execute("SELECT count(*) as count FROM " . TABLE_CUSTOMERS . " WHERE customers_newsletter = 1", false, true, 1800);
$newsletters = $result->fields['count'];

$counter = 0;
$counter_startdate_formatted = 'New';
$result = $db->Execute("SELECT startdate, counter FROM " . TABLE_COUNTER, false, true, 7200);
if ($result->RecordCount()) {
    $counter_startdate = $result->fields['startdate'];
    $counter = $result->fields['counter'];
    $counter_startdate_formatted = strftime(DATE_FORMAT_SHORT, mktime(0, 0, 0, substr($counter_startdate, 4, 2), substr($counter_startdate, -2), substr($counter_startdate, 0, 4)));
}


?>
<div class="panel panel-default reportBox">
    <div class="panel-heading header"><?php echo BOX_TITLE_STATISTICS; ?> </div>
    <table class="table table-striped table-condensed">
        <tr>
          <td> <?php echo BOX_ENTRY_COUNTER_DATE; ?></td>
          <td class="text-right"><?php echo $counter_startdate_formatted; ?></td>
        </tr>
        <tr>
          <td><?php echo BOX_ENTRY_COUNTER; ?></td>
          <td class="text-right"><?php echo $counter; ?></td>
        </tr>
<?php if (zen_is_superuser() || check_page(FILENAME_CUSTOMERS, '')) { ?>
      <tr>
        <td><?php echo BOX_ENTRY_CUSTOMERS; ?></td>
        <td class="text-right"><?php echo $customers; ?></td>
      </tr>
<?php } ?>
<?php if (zen_is_superuser() || check_page(FILENAME_PRODUCT, '')) { ?>
      <tr>
        <td><?php echo BOX_ENTRY_PRODUCTS; ?></td>
        <td class="text-right"><?php echo $products; ?></td>
      </tr>
      <tr>
        <td><?php echo BOX_ENTRY_PRODUCTS_OFF; ?></td>
        <td class="text-right"><?php echo $products_off; ?></td>
      </tr>
<?php } ?>
      <tr>
        <td><?php echo BOX_ENTRY_REVIEWS; ?></td>
        <td class="text-right"><?php echo $reviews; ?></td>
      </tr>
      <?php if (REVIEWS_APPROVAL == '1') { ?>
        <tr>
          <td><a href="<?php echo zen_href_link(FILENAME_REVIEWS, 'status=1'); ?>"><?php echo BOX_ENTRY_REVIEWS_PENDING; ?></a></td>
          <td class="text-right"><?php echo $reviews_pending; ?></td>
        </tr>
      <?php } ?>
      <tr>
        <td><?php echo BOX_ENTRY_NEWSLETTERS; ?></td>
        <td class="text-right"><?php echo $newsletters; ?></td>
      </tr>
    </table>
</div>

