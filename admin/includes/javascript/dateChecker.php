<?php
/**
 * dateChecker: A jQuery script to validate a 'datepicker' date.
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Dec 08 New in v2.0.0-alpha1 $
 */
// -----
// This module validates (via AJAX call) a datepicker-controlled date.  The expected HTML layout for a date
// to be checked using this script is:
//
// <div>
//   <div class="date ...">
//     ...
//     <input ... > ... controlled by the datepicker plugin
//     ...
//   </div>
//   ...
//   <span class="date-check-error">...</span>
// </div>
//
// On any change to, or when the mouse leaves, the datepicker input field, an AJAX call
// is issued to ensure that the entered date is valid.  If not, the date-class div is marked
// as having an error, the associated 'date-check-error' span is shown and the form's submit
// button is disabled.  If the date *is* valid, the date-class div's error class is removed,
// the associated 'date-check-error' is hidden and (if no other 'date-check-error' elements are
// currently shown) the form's submit button is enabled.
//
// Initially, this script is used by the admin's banner_manager.php, featured.php and specials.php.
//
?>
<script>
$(document).ready(function () {
    $('.date-check-error').hide();

    $('div.date input').on('change mouseleave', function() {
        let theDate = $(this);
        zcJS.ajax({
            url: 'ajax.php?act=ajaxAdminDatePickerDateCheck&method=check',
            data: {'date_to_check': theDate.val()}
        }).done(function( response ) {
            if (response === 'false') {
                $('button[type="submit"]').prop('disabled', true);
                theDate.parent('div.date').addClass('has-error').parent().find('.date-check-error').show();
            } else {
                theDate.parent('div.date').removeClass('has-error').parent().find('.date-check-error').hide();
                if ($('.date-check-error:visible').length === 0) {
                    $('button[type="submit"]').prop('disabled', false);
                }
            }
        });
    });
});
</script>
