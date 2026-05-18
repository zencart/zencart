<?php
/**
 * zen_jscript_form_check
 *
 * required by various pages' jscript_form_check.php
 * provides client-side form validation for various forms, with error messages defined in language files
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Modified in v3.0 $
 */
?>
<script title="zen_jscript_form_check (template_default)">
    "use strict";
    let selected; // retained for compatibility if referenced elsewhere

    function check_form_optional(form_name) {
        const f = form_name;
        if (!f.elements['firstname']) {
            return true;
        }

        const firstname = f.elements['firstname'].value;
        const lastname = f.elements['lastname'].value;
        const street_address = f.elements['street_address'].value;

        if (firstname === '' && lastname === '' && street_address === '') {
            return true;
        }
        return check_form(form_name);
    }

    let form = "";
    let submitted = false;
    let error = false;
    let error_message = "";

    function getElement(field_name) {
        return form && form.elements ? form.elements[field_name] : null;
    }

    function isVisibleInput(el) {
        return !!el && el.type !== "hidden";
    }

    function appendError(message) {
        error_message += "* " + message + "\n";
        error = true;
    }

    function check_input(field_name, field_size, message) {
        const el = getElement(field_name);
        if (!isVisibleInput(el) || field_size === 0) {
            return;
        }

        const field_value = el.value;
        if (field_value === '' || field_value.length < field_size) {
            appendError(message);
        }
    }

    function check_radio(field_name, message) {
        const el = getElement(field_name);
        if (!isVisibleInput(el)) {
            return;
        }

        // Handles both radio groups (NodeList/HTMLCollection) and single radio inputs
        const radios = (typeof el.length === "number" && !el.tagName) ? el : [el];
        let isChecked = false;

        for (let i = 0; i < radios.length; i++) {
            if (radios[i].checked === true) {
                isChecked = true;
                break;
            }
        }

        if (isChecked === false) {
            appendError(message);
        }
    }

    function check_select(field_name, field_default, message) {
        const el = getElement(field_name);
        if (!isVisibleInput(el)) {
            return;
        }

        if (el.value === field_default) {
            appendError(message);
        }
    }

    function check_password(field_name_1, field_name_2, field_size, message_1, message_2) {
        const p1 = getElement(field_name_1);
        const p2 = getElement(field_name_2);
        if (!isVisibleInput(p1) || !p2) {
            return;
        }

        const password = p1.value;
        const confirmation = p2.value;

        if (password === '' || password.length < field_size) {
            appendError(message_1);
        } else if (password !== confirmation) {
            appendError(message_2);
        }
    }

    function check_password_new(field_name_1, field_name_2, field_name_3, field_size, message_1, message_2, message_3) {
        const currentEl = getElement(field_name_1);
        const newEl = getElement(field_name_2);
        const confirmEl = getElement(field_name_3);
        if (!isVisibleInput(currentEl) || !newEl || !confirmEl) {
            return;
        }

        const password_current = currentEl.value;
        const password_new = newEl.value;
        const password_confirmation = confirmEl.value;

        if (password_current === '') {
            appendError(message_1);
        } else if (password_new === '' || password_new.length < field_size) {
            appendError(message_2);
        } else if (password_new !== password_confirmation) {
            appendError(message_3);
        }
    }

    function check_state(min_length, min_message, select_message) {
        const stateEl = getElement("state");
        const zoneEl = getElement("zone_id");

        if (stateEl && zoneEl) {
            if (!form.state.disabled && form.zone_id.value === "") {
                check_input("state", min_length, min_message);
            }
        } else if (stateEl && stateEl.type !== "hidden" && form.state.disabled) {
            check_select("zone_id", "", select_message);
        }
    }

    function check_form(form_name) {
        if (submitted === true) {
            alert("<?= JS_ERROR_SUBMITTED ?>");
            return false;
        }

        error = false;
        form = form_name;
        error_message = "<?= JS_ERROR ?>";

        <?php
        if (ACCOUNT_GENDER === 'true') { ?>
            check_radio("gender", "<?= ENTRY_GENDER_ERROR ?>");
        <?php }

        if ((int)ENTRY_FIRST_NAME_MIN_LENGTH > 0) { ?>
        check_input("firstname", <?= (int)ENTRY_FIRST_NAME_MIN_LENGTH ?>, "<?= ENTRY_FIRST_NAME_ERROR ?>");
        <?php }

        if ((int)ENTRY_LAST_NAME_MIN_LENGTH > 0) { ?>
        check_input("lastname", <?= (int)ENTRY_LAST_NAME_MIN_LENGTH ?>, "<?= ENTRY_LAST_NAME_ERROR ?>");
        <?php }

        if (ACCOUNT_DOB === 'true' && (int)ENTRY_DOB_MIN_LENGTH != 0) { ?>
        check_input("dob", <?= (int)ENTRY_DOB_MIN_LENGTH ?>, "<?= ENTRY_DATE_OF_BIRTH_ERROR ?>");
        <?php }

        if (ACCOUNT_COMPANY === 'true' && (int)ENTRY_COMPANY_MIN_LENGTH != 0) { ?>
        check_input("company", <?= (int)ENTRY_COMPANY_MIN_LENGTH ?>, "<?= ENTRY_COMPANY_ERROR ?>");
        <?php }

        if ((int)ENTRY_EMAIL_ADDRESS_MIN_LENGTH > 0) { ?>
        check_input("email_address", <?= (int)ENTRY_EMAIL_ADDRESS_MIN_LENGTH ?>, "<?= ENTRY_EMAIL_ADDRESS_ERROR ?>");
        <?php }

        if ((int)ENTRY_STREET_ADDRESS_MIN_LENGTH > 0) { ?>
        check_input("street_address", <?= (int)ENTRY_STREET_ADDRESS_MIN_LENGTH ?>, "<?= ENTRY_STREET_ADDRESS_ERROR ?>");
        <?php }

        if ((int)ENTRY_POSTCODE_MIN_LENGTH > 0) { ?>
        check_input("postcode", <?= (int)ENTRY_POSTCODE_MIN_LENGTH ?>, "<?= ENTRY_POST_CODE_ERROR ?>");
        <?php }

        if ((int)ENTRY_CITY_MIN_LENGTH > 0) { ?>
        check_input("city", <?= (int)ENTRY_CITY_MIN_LENGTH ?>, "<?= ENTRY_CITY_ERROR ?>");
        <?php }

        if (ACCOUNT_STATE === 'true') { ?>
        check_state(<?= (int)ENTRY_STATE_MIN_LENGTH ?>, "<?= ENTRY_STATE_ERROR ?>", "<?= ENTRY_STATE_ERROR_SELECT ?>");
        <?php } ?>

        check_select("country", "", "<?= ENTRY_COUNTRY_ERROR ?>");

        <?php
        if ((int)ENTRY_TELEPHONE_MIN_LENGTH > 0) { ?>
        check_input("telephone", <?= (int)ENTRY_TELEPHONE_MIN_LENGTH ?>, "<?= ENTRY_TELEPHONE_NUMBER_ERROR ?>");
        <?php }

        if ((int)ENTRY_PASSWORD_MIN_LENGTH > 0) { ?>
        check_password("password", "confirmation", <?= (int)ENTRY_PASSWORD_MIN_LENGTH ?>, "<?= ENTRY_PASSWORD_ERROR ?>", "<?= ENTRY_PASSWORD_ERROR_NOT_MATCHING ?>");
        check_password_new("password_current", "password_new", "password_confirmation", <?= (int)ENTRY_PASSWORD_MIN_LENGTH ?>, "<?= ENTRY_PASSWORD_ERROR ?>", "<?= ENTRY_PASSWORD_NEW_ERROR ?>", "<?= ENTRY_PASSWORD_NEW_ERROR_NOT_MATCHING ?>");
        <?php } ?>

        if (error === true) {
            alert(error_message);
            return false;
        }

        submitted = true;
        return true;
    }
</script>
