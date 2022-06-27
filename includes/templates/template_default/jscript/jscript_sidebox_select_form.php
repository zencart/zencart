<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jun 27 Created for v1.5.8 $
 */

// -----
// A jQuery script to handle the sidebox forms that require a selection via a 'select' tag
// and contain an associated 'submit' type input or button.
//
// On entry to the script:
//
// 1. Each form must contain the class 'sidebox-select-form'; multiple such forms can exist.
// 2. A single 'select' tag is present in each form.
//    a. If the 'select' tag includes a "Please Select" option, that option's value must be an empty string ('') and
//       the 'select' tag itself must include the 'required' attribute.
//
?>
<script>
jQuery(document).ready(function() {
<?php
    // -----
    // Cycle through each form with a class of 'sidebox-select-form' that has a select tag
    // marked as 'required'.
    //
?>
    jQuery('form.sidebox-select-form select:required').each(function() {
<?php
        // -----
        // If there's an option with an empty ('') value, mark that option as disabled and
        // remove its selected attribute.
        //
?>
        jQuery('option[value=""]', this).attr('disabled', true).removeAttr('selected');
<?php
        // -----
        // If a non-'' option is currently selected, ensure that the form's submit button is
        // enabled and change the cursor to a pointer.  Otherwise, disable the form's submit
        // button and change the cursor to indicate that the button is not allowed.
        //
?>
        if (jQuery('select option:selected', this).length > 0) {
            jQuery(this).siblings('input[type="submit"], button[type="submit"]').attr('disabled', false).css('cursor', 'pointer');
        } else {
            jQuery(this).siblings('input[type="submit"], button[type="submit"]').attr('disabled', true).css('cursor', 'not-allowed');
        }
<?php
        // -----
        // If an option in the select tag is selected, re-enable the submiit button and change the
        // cursor back to a pointer.
        //
?>
        jQuery(this).on('change', function() {
            jQuery(this).siblings('input[type="submit"], button[type="submit"]').attr('disabled', false).css('cursor', 'pointer');
        });
    });
});
</script>