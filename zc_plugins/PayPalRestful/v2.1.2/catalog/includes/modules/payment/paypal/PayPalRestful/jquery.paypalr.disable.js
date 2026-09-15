jQuery(document).ready(function() {
    if (jQuery('#pmt-paypalr').is(':checked')) {
        jQuery('input[name=payment][value=paypalr]').prop('checked', false).trigger('change');
    }
    jQuery('#pmt-paypalr').prop('disabled', true);
});