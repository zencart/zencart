<?php
/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 05 New in v1.5.8-alpha $
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
        // Iterate over each of the select tag's options, 'converting' any option with a value
        // of '' into an <optgroup>, adding the other options as <option> tags for that 'group' and then closing
        // up the <optgroup> tag.
        //
?>
        var theOptions = '';
        var optGroup = false;
        var isSelected = '';
        jQuery('option', this).each(function() {
            if (jQuery(this).val() == '') {
                optGroup = true;
                theOptions += '<optgroup label="'+jQuery(this).text()+'">';
            } else {
                isSelected = '';
                if (jQuery(this).is(':selected')) {
                    isSelected = ' selected="selected"';
                }
                theOptions += '<option value="'+jQuery(this).val()+'"'+isSelected+'>'+jQuery(this).text()+'</option>';
            }
        });
        if (optGroup === true) {
            theOptions += '</optgroup>';
        }
        jQuery(this).empty().append(theOptions);
        jQuery('optgroup', this).css({'font-style':'normal'});
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