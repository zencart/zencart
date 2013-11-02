/*
 * Zen Cart extensions to the Foundation Responsive Library http://foundation.zurb.com
 * @package templates
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:
 */
/*
 * based on Foundation 4.3
 */
/*jslint unparam: true, browser: true, indent: 2 */

;(function ($, window, document, undefined) {
  /*
   * override ABIDE to add support for checkbox "required"
   * override ABIDE to add support for "equalto" comparison
   */
  Foundation.libs.abide.check_validation_and_apply_styles = function(el_patterns) {
    var count = el_patterns.length,
    validations = [];
    for (var i = count - 1; i >= 0; i--) {
      var el = el_patterns[i][0],
      equalToChk = true;
      required = el_patterns[i][2],
      value = el.value,
      is_radio = el.type === "radio",
      is_radio = el.type === "checkbox",
      valid_length = (required) ? (el.value.length > 0) : true;
      if ($(el_patterns[i]).attr('equalto'))  {
        chkEl = $(el_patterns[i]).attr('equalto');
        elVal = $('[name="'+chkEl+'"]').val();
        if (elVal != value) {equalToChk = false;}
      }
      if (is_radio && required) {
        validations.push(this.valid_radio(el, required));
      } else {
        if (el_patterns[i][1].test(value) && valid_length && equalToChk ||
            !required && el.value.length < 1 && equalToChk) {
          $(el).removeAttr('data-invalid').parent().removeClass('error');
          validations.push(true);
        } else {
          $(el).attr('data-invalid', '').parent().addClass('error');
          validations.push(false);
        }
      }
    }
    return validations;
  }
  /*
   * override ABIDE to allow for data-abide-ajax-final attribute to intercept form submission
   */
  Foundation.libs.abide.validate = function(els, e) {
   var validations = this.parse_patterns(els),
   validation_count = validations.length,
   form = $(els[0]).closest('form');

    while (validation_count--) {
      if (!validations[validation_count] && /submit/.test(e.type)) {
        if (this.settings.focus_on_invalid) els[validation_count].focus();
        form.trigger('invalid');
      $(els[validation_count]).closest('form').attr('data-invalid', '');
      return false;
      }
    }
    if (/submit/.test(e.type)) {
      form.trigger('valid');
    }
    form.removeAttr('data-invalid');
    if (form.attr('data-abide-ajax-final') == '') {
      return false;
    } else {
      return true;
    }
  }
}(Foundation.zj, this, this.document));
