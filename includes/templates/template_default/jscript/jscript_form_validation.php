<?php
/**
 * form_check javascript
 * NOTE: Depends on zen_general.js
 *
 * @package page
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: form_check.js.php 17018 2010-07-27 07:25:41Z drbyte $
 */
?>
<script>
var submitted = false;
var form = "";
var error = false;
var error_message = "";

function check_account_address_form(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }

  error = false;
  form = form_name;
  error_message = "<?php echo JS_ERROR; ?>";

<?php if (ACCOUNT_GENDER == 'true') echo '  check_radio("gender", "' . ENTRY_GENDER_ERROR . '");' . "\n"; ?>

<?php if ((int)ENTRY_FIRST_NAME_MIN_LENGTH > 0) { ?>
  check_input("firstname", <?php echo (int)ENTRY_FIRST_NAME_MIN_LENGTH; ?>, "<?php echo ENTRY_FIRST_NAME_ERROR; ?>");
<?php } ?>
<?php if ((int)ENTRY_LAST_NAME_MIN_LENGTH > 0) { ?>
  check_input("lastname", <?php echo (int)ENTRY_LAST_NAME_MIN_LENGTH; ?>, "<?php echo ENTRY_LAST_NAME_ERROR; ?>");
<?php } ?>

<?php if (ACCOUNT_DOB == 'true' && (int)ENTRY_DOB_MIN_LENGTH != 0) echo '  check_input("dob", ' . (int)ENTRY_DOB_MIN_LENGTH . ', "' . ENTRY_DATE_OF_BIRTH_ERROR . '");' . "\n"; ?>
<?php if (ACCOUNT_COMPANY == 'true' && (int)ENTRY_COMPANY_MIN_LENGTH != 0) echo '  check_input("company", ' . (int)ENTRY_COMPANY_MIN_LENGTH . ', "' . ENTRY_COMPANY_ERROR . '");' . "\n"; ?>

<?php if ((int)ENTRY_EMAIL_ADDRESS_MIN_LENGTH > 0) { ?>
  check_input("email_address", <?php echo (int)ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>, "<?php echo ENTRY_EMAIL_ADDRESS_ERROR; ?>");
<?php } ?>
<?php if ((int)ENTRY_STREET_ADDRESS_MIN_LENGTH > 0) { ?>
  check_input("street_address", <?php echo (int)ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>, "<?php echo ENTRY_STREET_ADDRESS_ERROR; ?>");
<?php } ?>
<?php if ((int)ENTRY_POSTCODE_MIN_LENGTH > 0) { ?>
  check_input("postcode", <?php echo (int)ENTRY_POSTCODE_MIN_LENGTH; ?>, "<?php echo ENTRY_POST_CODE_ERROR; ?>");
<?php } ?>
<?php if ((int)ENTRY_CITY_MIN_LENGTH > 0) { ?>
  check_input("city", <?php echo (int)ENTRY_CITY_MIN_LENGTH; ?>, "<?php echo ENTRY_CITY_ERROR; ?>");
<?php } ?>
<?php if (ACCOUNT_STATE == 'true') { ?>
  check_state(<?php echo (int)ENTRY_STATE_MIN_LENGTH . ', "' . ENTRY_STATE_ERROR . '", "' . ENTRY_STATE_ERROR_SELECT . '"'; ?>);
<?php } ?>

  check_select("country", "", "<?php echo ENTRY_COUNTRY_ERROR; ?>");

<?php if ((int)ENTRY_TELEPHONE_MIN_LENGTH > 0) { ?>
  check_input("telephone", <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>, "<?php echo ENTRY_TELEPHONE_NUMBER_ERROR; ?>");
<?php } ?>

<?php if ((int)ENTRY_PASSWORD_MIN_LENGTH > 0) { ?>
  check_password("password", "confirmation", <?php echo (int)ENTRY_PASSWORD_MIN_LENGTH; ?>, "<?php echo ENTRY_PASSWORD_ERROR; ?>", "<?php echo ENTRY_PASSWORD_ERROR_NOT_MATCHING; ?>");
  check_password_new("password_current", "password_new", "password_confirmation", <?php echo (int)ENTRY_PASSWORD_MIN_LENGTH; ?>, "<?php echo ENTRY_PASSWORD_ERROR; ?>", "<?php echo ENTRY_PASSWORD_NEW_ERROR; ?>", "<?php echo ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING; ?>");
<?php } ?>

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}

function checkReviewsForm(form_name) {
  if (submitted == true) {
    alert("<?php echo JS_ERROR_SUBMITTED; ?>");
    return false;
  }
  form = form_name;
  error = false;
  error_message = "<?php echo JS_ERROR; ?>";

<?php if ((int)REVIEW_TEXT_MIN_LENGTH > 0) { ?>
  check_input('review_text',"<?php echo REVIEW_TEXT_MIN_LENGTH; ?>","<?php echo JS_REVIEW_TEXT; ?>");
<?php } ?>
  check_radio("rating","<?php echo JS_REVIEW_RATING; ?>");

  if (error == true) {
    alert(error_message);
    return false;
  } else {
    submitted = true;
    return true;
  }
}

function check_search_form() {
  var error_message = "<?php echo JS_ERROR; ?>";
  var error_found = false;
  var error_field;
  var keyword = document.advanced_search.keyword.value;
  var dfrom = document.advanced_search.dfrom.value;
  var dto = document.advanced_search.dto.value;
  var pfrom = document.advanced_search.pfrom.value;
  var pto = document.advanced_search.pto.value;
  var pfrom_float;
  var pto_float;

  if ( ((keyword == '') || (keyword.length < 1)) && ((dfrom == '') || (dfrom == '<?php echo DOB_FORMAT_STRING; ?>') || (dfrom.length < 1)) && ((dto == '') || (dto == '<?php echo DOB_FORMAT_STRING; ?>') || (dto.length < 1)) && ((pfrom == '') || (pfrom.length < 1)) && ((pto == '') || (pto.length < 1)) ) {
    error_message = error_message + "* <?php echo ERROR_AT_LEAST_ONE_INPUT; ?>\n";
    error_field = document.advanced_search.keyword;
    error_found = true;
  }

  if ((dfrom.length > 0) && (dfrom != '<?php echo DOB_FORMAT_STRING; ?>')) {
    if (!IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dfrom;
      error_found = true;
    }
  }

  if ((dto.length > 0) && (dto != '<?php echo DOB_FORMAT_STRING; ?>')) {
    if (!IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>')) {
      error_message = error_message + "* <?php echo ERROR_INVALID_TO_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if ((dfrom.length > 0) && (dfrom != '<?php echo DOB_FORMAT_STRING; ?>') && (IsValidDate(dfrom, '<?php echo DOB_FORMAT_STRING; ?>')) && (dto.length > 0) && (dto != '<?php echo DOB_FORMAT_STRING; ?>') && (IsValidDate(dto, '<?php echo DOB_FORMAT_STRING; ?>'))) {
    if (!CheckDateRange(document.advanced_search.dfrom, document.advanced_search.dto)) {
      error_message = error_message + "* <?php echo ERROR_TO_DATE_LESS_THAN_FROM_DATE; ?>\n";
      error_field = document.advanced_search.dto;
      error_found = true;
    }
  }

  if (pfrom.length > 0) {
    pfrom_float = parseFloat(pfrom);
    if (isNaN(pfrom_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_FROM_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pfrom;
      error_found = true;
    }
  } else {
    pfrom_float = 0;
  }

  if (pto.length > 0) {
    pto_float = parseFloat(pto);
    if (isNaN(pto_float)) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_MUST_BE_NUM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  } else {
    pto_float = 0;
  }

  if ( (pfrom.length > 0) && (pto.length > 0) ) {
    if ( (!isNaN(pfrom_float)) && (!isNaN(pto_float)) && (pto_float < pfrom_float) ) {
      error_message = error_message + "* <?php echo ERROR_PRICE_TO_LESS_THAN_PRICE_FROM; ?>\n";
      error_field = document.advanced_search.pto;
      error_found = true;
    }
  }

  if (error_found == true) {
    alert(error_message);
    error_field.focus();
    return false;
  } else {
    return true;
  }
}


// validate that the from date is less than or equal to the 'to' date
function CheckDateRange(from, to) {
  return (Date.parse(from.value) <= Date.parse(to.value));
}

// validate supplied data against selected format
function IsValidDate(DateToCheck, FormatString) {
  var strDateToCheck;
  var strDateToCheckArray;
  var strFormatArray;
  var strFormatString;
  var strDay;
  var strMonth;
  var strYear;
  var intday;
  var intMonth;
  var intYear;
  var intDateSeparatorIdx = -1;
  var intFormatSeparatorIdx = -1;
  var strSeparatorArray = new Array("-"," ","/",".");
  var strMonthArray = new Array("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");
  var intDaysArray = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

  strDateToCheck = DateToCheck.toLowerCase();
  strFormatString = FormatString.toLowerCase();

  if (strDateToCheck.length != strFormatString.length) {
    return false;
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strFormatString.indexOf(strSeparatorArray[i]) != -1) {
      intFormatSeparatorIdx = i;
      break;
    }
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strDateToCheck.indexOf(strSeparatorArray[i]) != -1) {
      intDateSeparatorIdx = i;
      break;
    }
  }

  if (intDateSeparatorIdx != intFormatSeparatorIdx) {
    return false;
  }

  if (intDateSeparatorIdx != -1) {
    strFormatArray = strFormatString.split(strSeparatorArray[intFormatSeparatorIdx]);
    if (strFormatArray.length != 3) {
      return false;
    }

    strDateToCheckArray = strDateToCheck.split(strSeparatorArray[intDateSeparatorIdx]);
    if (strDateToCheckArray.length != 3) {
      return false;
    }

    for (i=0; i<strFormatArray.length; i++) {
      if (strFormatArray[i] == 'mm' || strFormatArray[i] == 'mmm') {
        strMonth = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'dd') {
        strDay = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'yyyy') {
        strYear = strDateToCheckArray[i];
      }
    }
  } else {
    if (FormatString.length > 7) {
      if (strFormatString.indexOf('mmm') == -1) {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mm'), 2);
      } else {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mmm'), 3);
      }

      strDay = strDateToCheck.substring(strFormatString.indexOf('dd'), 2);
      strYear = strDateToCheck.substring(strFormatString.indexOf('yyyy'), 2);
    } else {
      return false;
    }
  }

  if (strYear.length != 4) {
    return false;
  }

  intday = parseInt(strDay, 10);
  if (isNaN(intday)) {
    return false;
  }
  if (intday < 1) {
    return false;
  }

  intMonth = parseInt(strMonth, 10);
  if (isNaN(intMonth)) {
    for (i=0; i<strMonthArray.length; i++) {
      if (strMonth == strMonthArray[i]) {
        intMonth = i+1;
        break;
      }
    }
    if (isNaN(intMonth)) {
      return false;
    }
  }
  if (intMonth > 12 || intMonth < 1) {
    return false;
  }

  intYear = parseInt(strYear, 10);
  if (isNaN(intYear)) {
    return false;
  }
  if (IsLeapYear(intYear) == true) {
    intDaysArray[1] = 29;
  }

  if (intday > intDaysArray[intMonth - 1]) {
    return false;
  }

  return true;
}

// is supplied year a leap year?
function IsLeapYear(intYear) {
  if (intYear % 100 == 0) {
    if (intYear % 400 == 0) {
      return true;
    }
  } else {
    if ((intYear % 4) == 0) {
      return true;
    }
  }

  return false;
}

// form-check, used to ensure that at least firstname, lastname, and street_address contain data. If not, fire full form validation
function check_form_optional(form_name) {
  var form = form_name;
  if (!form.elements['firstname']) {
    return true;
  } else {
    var firstname = form.elements['firstname'].value;
    var lastname = form.elements['lastname'].value;
    var street_address = form.elements['street_address'].value;

    if (firstname == '' && lastname == '' && street_address == '') {
      return true;
    } else {
      return check_form(form_name);
    }
  }
}
// validate that a given field contains text of a minimum length
function check_input(field_name, field_size, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    if (field_size == 0) return;
    var field_value = form.elements[field_name].value;

    if (field_value == '' || field_value.length < field_size) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
// validate that a given radio-button field has at least one choice selected
function check_radio(field_name, message) {
  var isChecked = false;

  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var radio = form.elements[field_name];

    for (var i=0; i<radio.length; i++) {
      if (radio[i].checked == true) {
        isChecked = true;
        break;
      }
    }

    if (isChecked == false) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
// validate that a "select" field has a valid selection chosen
function check_select(field_name, field_default, message) {
  if (form.elements[field_name] && (form.elements[field_name].type != "hidden")) {
    var field_value = form.elements[field_name].value;

    if (field_value == field_default) {
      error_message = error_message + "* " + message + "\n";
      error = true;
    }
  }
}
// validate whether password field is of sufficient length and confirmation matches
function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password = form.elements[field_name_1].value;
    var confirmation = form.elements[field_name_2].value;

    if (password == '' || password.length < field_size) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password != confirmation) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    }
  }
}
// validate "new password" and "confirmation" fields to be matching and of sufficient length
function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
  if (form.elements[field_name_1] && (form.elements[field_name_1].type != "hidden")) {
    var password_current = form.elements[field_name_1].value;
    var password_new = form.elements[field_name_2].value;
    var password_confirmation = form.elements[field_name_3].value;

    if (password_current == '' ) {
      error_message = error_message + "* " + message_1 + "\n";
      error = true;
    } else if (password_new == '' || password_new.length < field_size) {
      error_message = error_message + "* " + message_2 + "\n";
      error = true;
    } else if (password_new != password_confirmation) {
      error_message = error_message + "* " + message_3 + "\n";
      error = true;
    }
  }
}

// checks minimum length for "state"/province input field
function check_state(min_length, min_message, select_message) {
  if (form.elements["state"] && form.elements["zone_id"]) {
    if (!form.state.disabled && form.zone_id.value == "") check_input("state", min_length, min_message);
  } else if (form.elements["state"] && form.elements["state"].type != "hidden" && form.state.disabled) {
    check_select("zone_id", "", select_message);
  }
}

</script>
