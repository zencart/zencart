jQuery(document).ready(function() {
    function hidePprCcFields()
    {
        jQuery('.ppr-cc').each(function() {
            jQuery(this).hide();
            jQuery(this).prev('label').hide();
            jQuery(this).next('br, div.p-2').hide();
        });
        jQuery('#paypalr_collects_onsite').val('');
    }
    function showPprCcFields()
    {
        jQuery('.ppr-cc').each(function() {
            jQuery(this).show();
            jQuery(this).prev('label').show();
            jQuery(this).next('br, div.p-2').show();
        });
        jQuery('#paypalr_collects_onsite').val(1);
    }

    if (jQuery('#pmt-paypalr').is(':not(:checked)') || jQuery('#ppr-card').is(':not(:checked)')) {
        hidePprCcFields();
        if (jQuery('#pmt-paypalr').is(':not(:checked)') && jQuery('#pmt-paypalr').is(':radio')) {
            jQuery('#ppr-paypal, #ppr-card').prop('checked', false);
        } else if (jQuery('#pmt-paypalr').is(':not(:radio)')) {
            jQuery('#ppr-paypal').prop('checked', true);
        }
    }

    jQuery('input[name=payment]').on('change', function() {
        if (jQuery('#pmt-paypalr').is(':not(:checked)')) {
            jQuery('#ppr-paypal, #ppr-card').prop('checked', false);
        } else if (jQuery('#ppr-paypal').is(':not(:checked)') && jQuery('#ppr-card').is(':not(:checked)')) {
            jQuery('#ppr-paypal').prop('checked', true);
        }
        if (jQuery('#ppr-card').is(':checked')) {
            showPprCcFields();
        } else {
            hidePprCcFields();
        }
    });

    jQuery('#ppr-paypal, #ppr-card').on('change', function() {
        if (jQuery('#pmt-paypalr').is(':not(:checked)') && jQuery('#pmt-paypalr').is(':radio')) {
            jQuery('input[name=payment]').prop('checked', false);
            jQuery('input[name=payment][value=paypalr]').prop('checked', true).trigger('change');
        }
        if (jQuery('#ppr-card').is(':checked')) {
            showPprCcFields();
        } else {
            hidePprCcFields();
        }
    });
});