<?php
/**
 * functions_email.php
 * Passthru wrappers for the Email class.
 * All logic lives in includes/classes/Email.php
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Jun 15 Modified in v3.0.0 $
 */

/**
 * Set email system debugging off or on
 * 0=off
 * 1=show SMTP status errors
 * 2=show SMTP server responses
 * 4=show SMTP readlines if applicable
 * 5=maximum information, and output it to error_log
 * 'preview' to show HTML-emails on-screen while sending
 */
zen_define_default('EMAIL_SYSTEM_DEBUG', 0);
zen_define_default('EMAIL_ATTACHMENTS_ENABLED', true);

/**
 * enable embedded image support
 */
zen_define_default('EMAIL_ATTACH_EMBEDDED_IMAGES', 'Yes');

/**
 * If you need to force an authentication protocol, enter 'ssl' or 'tls'
 * Note that selecting a gmail server or port 465 will automatically select 'ssl' for you.
 */
zen_define_default('SMTPAUTH_EMAIL_PROTOCOL', 'none');

/**
 * Proxy function to send an email.
 * @see Email::send()
 *
 * @since ZC v1.0.3
 */
function zen_mail(string $to_name, string $to_address, string $email_subject, string $email_text, string $from_email_name, string $from_email_address, array|string $block = [], string $module = 'default', array|string $attachments_list = [], string $email_reply_to_name = '', string $email_reply_to_address = ''): false|string
{
    return Email::getInstance()->send($to_name, $to_address, $email_subject, $email_text, $from_email_name, $from_email_address, $block, $module, $attachments_list, $email_reply_to_name, $email_reply_to_address);
}

/**
 * Build HTML email content from a template.
 * @deprecated Use Email::buildHtmlFromTemplate() if needed outside this file; this wrapper exists for BC only.
 * @see Email::buildHtmlFromTemplate()
 *
 * @since ZC v1.2.0d
 */
function zen_build_html_email_from_template(string $module = 'default', array|string $content = ''): string
{
    return Email::getInstance()->buildHtmlFromTemplate($module, $content);
}

/**
 * Build the $extra_info block for admin copies of emails.
 * @see Email::collectExtraInfo()
 *
 * @since ZC v1.2.0d
 */
function email_collect_extra_info(string $from, string $email_from, string $login, string $login_email, string $login_phone = '', string $login_fax = '', array $moreinfo = []): array
{
    return Email::collectExtraInfo($from, $email_from, $login, $login_email, $login_phone, $login_fax, $moreinfo);
}

/**
 * Validate an email address.
 * @see Email::validateAddress()
 *
 * @since ZC v1.0.3
 */
function zen_validate_email(string $email): bool
{
    return Email::getInstance()->validateAddress($email);
}

/**
 * Proxy function to get a customer email address by customer ID.
 * NOTE: For the current customer, it is better to retrieve this from the in-memory Customer object.
 * @see Email::getAddressFromCustomerId()
 *
 * @since ZC v1.5.5
 */
function zen_get_email_from_customers_id(int|string $customers_id): string
{
    return Email::getAddressFromCustomerId($customers_id);
}

/**
 * Proxy function to prepare a string for DB input without escaping HTML entities.
 * @deprecated Use Email::dbPrepareInputHtmlSafe() if needed outside this file; this wrapper exists for BC only.
 * @see Email class (private method dbPrepareInputHtmlSafe)
 *
 * @since ZC v1.5.6b
 */
function zen_db_prepare_input_html_safe(string|array $string): string|array
{
    if (is_string($string)) {
        return trim(stripslashes($string));
    }

    if (is_array($string)) {
        foreach ($string as $key => $value) {
            $string[$key] = zen_db_prepare_input($value);
        }
    }
    return $string;
}
