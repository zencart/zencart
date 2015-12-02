<?php
/**
 * tpl_modules_checkout_address_book.php
 *
 * @package templateSystem
 * @copyright Copyright 2003-2009 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_checkout_address_book.php 13799 2009-07-08 02:08:33Z drbyte $
 */
?>
<?php
/**
 * require code to get address book details
 */
  require(DIR_WS_MODULES . zen_get_module_directory('checkout_address_book.php'));
?>

<?php
      while (!$addresses->EOF) {
        if ($addresses->fields['address_book_id'] == $_SESSION['sendto']) {
          echo '      <div id="defaultSelected" class="moduleRowSelected">' . "\n";
        } else {
          echo '      <div class="moduleRow">' . "\n";
        }
?>
        <div class="back"><?php echo zen_draw_radio_field('address', $addresses->fields['address_book_id'], ($addresses->fields['address_book_id'] == $_SESSION['sendto']), 'id="name-' . $addresses->fields['address_book_id'] . '"'); ?></div>
        <div class="back"><label for="name-<?php echo $addresses->fields['address_book_id']; ?>"><?php echo zen_output_string_protected($addresses->fields['firstname'] . ' ' . $addresses->fields['lastname']); ?></label></div>
      </div>
      <br class="clearBoth" />
       <address><?php echo zen_address_format(zen_get_address_format_id($addresses->fields['country_id']), $addresses->fields, true, ' ', '<br />'); ?></address>

<?php
        $addresses->MoveNext();
      }
?>