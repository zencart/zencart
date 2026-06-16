<?php

declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Jun 15  New in v3.0.0 $
 * @since ZC v3.0.0
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Zencart\Traits\NotifierManager;

/**
 * Central email handling class.
 * All public methods are also available as procedural wrappers in functions/functions_email.php.
 */
class Email
{
    use NotifierManager;

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /**
     * Send email. This is the central mail function.
     * If using "PHP" transport method, the SMTP Server or other mail application should be configured correctly in server's php.ini
     *
     * @param string $to_name The name of the recipient
     * @param string $to_address The email address of the recipient
     * @param string $email_subject The subject of the email
     * @param string $email_text The text of the email, may contain HTML entities
     * @param string $from_email_name The name of the sender
     * @param string $from_email_address The email address of the sender
     * @param array|string $block Array containing values to be inserted into HTML-based email template (string is a special case for overriding)
     * @param string $module The module name of the routine calling zen_mail; used for HTML template selection and email archiving
     * @param array|string $attachments_list Array of attachment names/mime-types to be included
     * @param string $email_reply_to_name Name of the "reply-to" header
     * @param string $email_reply_to_address Email address for reply-to header
     * @throws Exception
     */
    public function send(
        string $to_name,
        string $to_address,
        string $email_subject,
        string $email_text,
        string $from_email_name,
        string $from_email_address,
        array|string $block = [],
        string $module = 'default',
        array|string $attachments_list = [],
        string $email_reply_to_name = '',
        string $email_reply_to_address = ''
    ): false|string {
        global $db, $messageStack;
        if (zen_config('SEND_EMAILS') !== 'true') {
            return false;
        }

        if (defined('DEVELOPER_OVERRIDE_EMAIL_STATUS') && DEVELOPER_OVERRIDE_EMAIL_STATUS === 'false') {
            return false;
        }

        if (defined('EMAIL_MODULES_TO_SKIP') && in_array($module, explode(',', constant('EMAIL_MODULES_TO_SKIP')), true)) {
            return false;
        }

        // Filter against invalid characters
        foreach ([$from_email_address, $to_address, $from_email_name, $to_name, $email_subject] as $key => $value) {
            if (str_contains($value, "\r") || str_contains($value, "\n")) {
                return false;
            }
        }

        // If blank, abort.
        if (empty($block['EMAIL_MESSAGE_HTML']) && trim($email_text) === '') {
            return false;
        }

        // Parse from/to addresses
        if (preg_match("/ *([^<]*) *<([^>]*)> */i", $from_email_address, $regs)) {
            $from_email_name = trim($regs[1]);
            $from_email_address = $regs[2];
        }
        if (empty($from_email_name) || $from_email_name === $from_email_address) {
            $from_email_name = zen_config('STORE_NAME');
        }

        // ITERATE OVER ALL RECIPIENTS
        foreach (explode(',', $to_address) as $key => $value) {
            if (preg_match("/ *([^<]*) *<([^>]*)> */i", $value, $regs)) {
                $to_name = str_replace('"', '', trim($regs[1]));
                $to_email_address = $regs[2];
            } elseif (preg_match("/ *([^ ]*) */i", $value, $regs)) {
                $to_email_address = trim($regs[1]);
            }
            if (!isset($to_email_address)) {
                $to_email_address = trim($to_address);
            }

            $this->notify('NOTIFY_EMAIL_ADDRESS_TEST', [], $to_name, $to_email_address, $email_subject);
            if (!$this->validateAddress($to_email_address)) {
                $this->notify('NOTIFY_EMAIL_ADDRESS_VALIDATION_FAILURE', sprintf(EMAIL_SEND_FAILED . ' (failed validation)', $to_name, $to_email_address, $email_subject));
                error_log(sprintf(EMAIL_SEND_FAILED . ' (failed validation)', $to_name, $to_email_address, $email_subject));
                continue;
            }

            // CHECK HTML MESSAGE $block FOR OVERRIDES
            if (is_array($block)) {
                if (empty($block['EMAIL_TO_NAME'])) {
                    $block['EMAIL_TO_NAME'] = $to_name;
                }
                if (empty($block['EMAIL_TO_ADDRESS'])) {
                    $block['EMAIL_TO_ADDRESS'] = $to_email_address;
                }
                if (empty($block['EMAIL_SUBJECT'])) {
                    $block['EMAIL_SUBJECT'] = $email_subject;
                }
                if (empty($block['EMAIL_FROM_NAME'])) {
                    $block['EMAIL_FROM_NAME'] = $from_email_name;
                }
                if (empty($block['EMAIL_FROM_ADDRESS'])) {
                    $block['EMAIL_FROM_ADDRESS'] = $from_email_address;
                }
                if (empty($block['EMAIL_MESSAGE_HTML'])) {
                    $block['EMAIL_MESSAGE_HTML'] = $email_text;
                }
            }

            // BUILD THE HTML MESSAGE from submitted $block and relevant HTML template
            // NOTE: Both $email_html and $email_text are now strings from this point forward.
            $email_html = (!is_array($block) && str_starts_with($block, '<html>')) ? $block : $this->buildHtmlFromTemplate($module, $block);

            // abort html portion if specified in incoming $block
            if ($block === '' || $block === 'none') {
                $email_html = '';
            }

            $email_text = $this->sanitizeTextContent($email_text, is_array($block) ? ($block['EMAIL_MESSAGE_HTML'] ?? '') : '', $module, $to_email_address);

            $text = stripslashes($email_text);
            $email_html = stripslashes($email_html);

            // DETERMINE RECIPIENT EMAIL FORMAT (TEXT/HTML)
            $sql = "SELECT customers_email_format FROM " . TABLE_CUSTOMERS . " WHERE customers_email_address= :custEmailAddress:";
            $sql = $db->bindVars($sql, ':custEmailAddress:', $to_email_address, 'string');
            $result = $db->Execute($sql, 1);
            $customers_email_format = (!$result->EOF) ? $result->fields['customers_email_format'] : '';

            $this->notify('NOTIFY_EMAIL_DETERMINING_EMAIL_FORMAT', $to_email_address, $customers_email_format, $module);

            // If recipient type is one that denotes skipping, skip.
            if ($customers_email_format === 'NONE' || $customers_email_format === 'OUT') {
                continue;
            }

            if (zen_config('ADMIN_EXTRA_EMAIL_FORMAT') === 'TEXT' && str_ends_with($module, '_extra')) {
                $email_html = '';
            }
            if ($customers_email_format === '' && zen_config('ADMIN_EXTRA_EMAIL_FORMAT') === 'HTML' && in_array($module, ['newsletters', 'product_notification']) && isset($_SESSION['admin_id'])) {
                $customers_email_format = 'HTML';
            }

            if ($module === 'xml_record') {
                $email_html = '';
                $customers_email_format = 'TEXT';
            }

            $this->notify('NOTIFY_EMAIL_AFTER_EMAIL_FORMAT_DETERMINED');

            // CREATE A NEW MAILER INSTANCE
            $mail = new PHPMailer();
            $mail->XMailer = 'Self-Hosted Zen Cart merchant';

            $lang_code = strtolower(($_SESSION['languages_code'] === '' ? 'en' : $_SESSION['languages_code']));
            $mail::setLanguage($lang_code);

            $mail->CharSet = (defined('CHARSET')) ? CHARSET : 'iso-8859-1';
            if (defined('EMAIL_ENCODING_METHOD') && EMAIL_ENCODING_METHOD !== '') {
                $mail->Encoding = EMAIL_ENCODING_METHOD;
            }
            if ((int)EMAIL_SYSTEM_DEBUG > 0) {
                $mail->SMTPDebug = (int)EMAIL_SYSTEM_DEBUG;
            }
            if ((int)EMAIL_SYSTEM_DEBUG > 4) {
                $mail->Debugoutput = 'error_log';
            }

            // SET UP EMAIL CONNECTION DETAILS WITH SMTP CREDENTIALS
            $email_transport = $this->setupEmailTransport($module, $mail);

            $mail->Subject = $email_subject;

            // SET UP SENDER AND REPLY-TO ADDRESSES
            if ($email_transport === 'sendmail-f' || zen_config('EMAIL_SEND_MUST_BE_STORE') === 'Yes') {
                $mail->Sender = zen_config('EMAIL_FROM');
            }

            if (defined('EMAIL_REPLY_TO_OVERRIDE') && zen_validate_email(EMAIL_REPLY_TO_OVERRIDE)) {
                $email_reply_to_address = (!empty($email_reply_to_address)) ? $email_reply_to_address : EMAIL_REPLY_TO_OVERRIDE;
            }

            $email_reply_to_address = (!empty($email_reply_to_address)) ? $email_reply_to_address : (in_array($module, ['contact_us', 'ask_a_question', 'checkout_extra']) ? $from_email_address : zen_config('EMAIL_FROM'));
            $email_reply_to_name = (!empty($email_reply_to_name)) ? $email_reply_to_name : (in_array($module, ['contact_us', 'ask_a_question', 'checkout_extra']) ? $from_email_name : zen_config('STORE_NAME'));
            $mail->addReplyTo($email_reply_to_address, $email_reply_to_name);

            $mail->setFrom($from_email_address, $from_email_name);
            if (zen_config('EMAIL_SEND_MUST_BE_STORE') === 'Yes') {
                $mail->From = zen_config('EMAIL_FROM');
            }
            if (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS !== '') {
                $to_email_address = DEVELOPER_OVERRIDE_EMAIL_ADDRESS;
                if (!$this->validateAddress($to_email_address)) {
                    error_log(sprintf(EMAIL_SEND_FAILED . ' (devEmail failed validation)', $to_name, $to_email_address, $email_subject));
                    continue;
                }
            }

            // SPECIFY THE RECIPIENT ADDRESS
            $mail->addAddress($to_email_address, $to_name);

            // HANDLE IMAGES
            if (zen_config('EMAIL_USE_HTML') === 'true') {
                $email_html = $this->processEmbeddedImages($email_html, $mail);
            }

            // HANDLE ATTACHMENTS
            $this->processFileAttachments($attachments_list, $module, $mail);

            // HANDLE HTML / TEXT PARTS
            if (zen_config('EMAIL_USE_HTML') === 'true' && trim($email_html) !== ''
                && ($customers_email_format === 'HTML' || (zen_config('ADMIN_EXTRA_EMAIL_FORMAT') !== 'TEXT' && str_ends_with($module, '_extra')))) {
                $mail->msgHTML($email_html);
                if ($text !== '') {
                    $mail->AltBody = $text;
                }
            } else {
                $mail->Body = $text;
            }

            // SET NECESSARY HEADERS
            if (in_array($module, ['newsletters', 'product_notification'])) {
                $mail->addCustomHeader('Precedence: bulk');
            }

            $mail->addCustomHeader('Auto-Submitted: auto-generated');

            // SEND
            $ErrorInfo = $this->dispatchMail($mail, $to_name, $to_email_address, $email_subject, $from_email_name, $from_email_address, $email_html, $text, $module);

            // ARCHIVE THE EMAIL IF NECESSARY
            if (zen_config('EMAIL_ARCHIVE') === 'true' && $module !== 'password_forgotten_admin' && $module !== 'cc_middle_digs' && $module !== 'no_archive') {
                $this->archiveWrite($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, (string)$email_html, $text, $module, $ErrorInfo);
            }

            // REPORT OR LOG ANY ERRORS
            if ($ErrorInfo !== '') {
                $mail_langs = $mail->getTranslations();
                if (!str_contains($ErrorInfo, $mail_langs['recipients_failed'])) {
                    if (!str_contains($ErrorInfo, 'spam content')) {
                        trigger_error('Email Error: ' . $ErrorInfo);
                    }
                } else {
                    $log_prefix = (IS_ADMIN_FLAG === true) ? '/myDEBUG-bounced-email-adm-' : '/myDEBUG-bounced-email-';
                    $log_date = new DateTime();
                    error_log('Request URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL . PHP_EOL . $ErrorInfo, 3, DIR_FS_LOGS . $log_prefix . $log_date->format('Ymd-His-u') . '.log');
                }
            }

            // ITERATES TO NEXT RECIPIENT ADDRESS in foreach
        }

        $this->notify('NOTIFY_EMAIL_AFTER_SEND_ALL_SPECIFIED_ADDRESSES');

        return isset($ErrorInfo) ? nl2br($ErrorInfo, false) : '';
    }

    /**
     * Strip tags, append disclaimers, and decode entities from the plain-text portion of an outgoing email.
     */
    private function sanitizeTextContent(string $email_text, string $html_fallback, string $module, string $to_email_address): string
    {
        // if no text portion provided, build text-only portion from html content
        if ($email_text === '') {
            $email_text = str_replace(
                ['<br>', '<br />', '</p>'],
                ["<br>\n", "<br>\n", "</p>\n"],
                $html_fallback
            );
            $email_text = ($module !== 'xml_record') ? zen_output_string_protected(stripslashes(strip_tags($email_text))) : $email_text;
        } elseif ($module !== 'xml_record') {
            // Strip potentially nefarious tags while preserving a safe subset
            $email_text = preg_replace('~</?([^(strong>|br ?\/?>|a href=|p |span|script|li|ol|ul|em|b>|i>|u>)])~', '@lt@\\1', $email_text);
            $email_text = strip_tags($email_text);
            $email_text = str_replace('@lt@', '<', $email_text);
        }

        // TRANSACTIONAL EMAILS GET DISCLAIMERS
        if ($this->isNonTransactional($module)) {
            if (defined('EMAIL_DISCLAIMER') && EMAIL_DISCLAIMER !== '' && !str_contains($email_text, sprintf(EMAIL_DISCLAIMER, zen_config('STORE_OWNER_EMAIL_ADDRESS'))) && $to_email_address !== zen_config('STORE_OWNER_EMAIL_ADDRESS') && !defined('EMAIL_DISCLAIMER_NEW_CUSTOMER')) {
                $email_text .= "\n" . sprintf(EMAIL_DISCLAIMER, zen_config('STORE_OWNER_EMAIL_ADDRESS'));
            }
            if (defined('EMAIL_SPAM_DISCLAIMER') && EMAIL_SPAM_DISCLAIMER !== '' && !str_contains($email_text, EMAIL_SPAM_DISCLAIMER) && $to_email_address !== zen_config('STORE_OWNER_EMAIL_ADDRESS')) {
                $email_text .= "\n\n" . EMAIL_SPAM_DISCLAIMER;
            }
        }

        // CLEAN UP CHARACTERS
        $email_text = preg_replace('/((&amp;)|&)+/', '&', $email_text);

        if (!empty(zen_config('CURRENCIES_TRANSLATIONS'))) {
            $zen_fix_currencies = preg_split("/[:,]/", str_replace(' ', '', zen_config('CURRENCIES_TRANSLATIONS')));
            $size = count($zen_fix_currencies);
            for ($i = 0, $n = $size; $i < $n; $i += 2) {
                if (empty($zen_fix_currencies[$i + 1])) {
                    break;
                }
                $zen_fix_current = $zen_fix_currencies[$i];
                $zen_fix_replace = $zen_fix_currencies[$i + 1];
                if ($zen_fix_current !== '') {
                    while (str_contains($email_text, $zen_fix_current)) {
                        $email_text = str_replace($zen_fix_current, $zen_fix_replace, $email_text);
                    }
                }
            }
        }

        $email_text = str_replace(
            ['&quot;', '&lt;', '&gt;', "\x00"],
            ['"', '<', '>', ' '],
            $email_text
        );
        $email_text = str_replace('&nbsp;', ' ', $email_text);

        return $email_text;
    }

    /**
     * Transactional emails don't need disclaimers; these module names are marketing-related or first-contact.
     */
    private function isNonTransactional(string $email_module_name): bool
    {
        return in_array($email_module_name, [
            'newsletters',
            'product_notification',
            'direct_email',
            'coupon',
            'gv_mail',
            'welcome',
        ]);
    }

    /**
     * Mask spammy server headers, dispatch the mailer, restore headers, and return any error info string.
     */
    private function dispatchMail(PHPMailer $mail, string $to_name, string $to_email_address, string $email_subject, string $from_email_name, string $from_email_address, string $email_html, string $text, string $module): string
    {
        global $messageStack;

        // BLOCK ANY SPAMMY HEADERS THAT PHP MIGHT ADD
        $oldVars = [];
        $tmpVars = ['REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'PHP_SELF', $mail->Mailer === 'smtp' ? 'a-placeholder-key-that-wont-exist' : 'SERVER_NAME'];
        foreach ($tmpVars as $key) {
            if (isset($_SERVER[$key])) {
                $oldVars[$key] = $_SERVER[$key];
                $_SERVER[$key] = '';
            }
            if ($key === 'REMOTE_ADDR') {
                $_SERVER[$key] = HTTP_SERVER;
            } elseif ($key === 'PHP_SELF') {
                $_SERVER[$key] = '/obf' . 'us' . 'cated';
            }
        }
        ini_set('mail.add_x_header', 0);

        $defaultHostname = preg_replace('~(^https?://|\/.*$)~', '', defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER);
        $mail->Hostname = defined('EMAIL_HOSTNAME') ? EMAIL_HOSTNAME : $defaultHostname;

        $this->notify('NOTIFY_EMAIL_READY_TO_SEND', [$mail], $mail);

        // SEND THE EMAIL
        $ErrorInfo = '';
        $success = false;
        try {
            $success = $mail->send();
        } catch (Exception $e) {
        }
        if (!$success) {
            $msg = sprintf(EMAIL_SEND_FAILED, $to_name, $to_email_address, $email_subject) . '&nbsp;' . $mail->ErrorInfo;
            if ($messageStack !== null) {
                if (IS_ADMIN_FLAG === true) {
                    $messageStack->add_session($msg, 'error');
                } else {
                    $messageStack->add('header', $msg, 'error');
                }
            } else {
                error_log($msg);
            }
            $ErrorInfo .= ($mail->ErrorInfo !== '') ? $mail->ErrorInfo . "\n" : '';
        }
        $this->notify('NOTIFY_EMAIL_AFTER_SEND');

        // RESET ANY TEMPORARY SERVER HEADER VARIABLES
        foreach ($oldVars as $key => $val) {
            $_SERVER[$key] = $val;
        }

        $this->notify('NOTIFY_EMAIL_AFTER_SEND_WITH_ALL_PARAMS', [$to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module, $ErrorInfo]);

        return $ErrorInfo;
    }

    /**
     * Validate an email address.
     */
    public function validateAddress(string $email): bool
    {
        $valid_address = true;

        if (substr_count($email, '@') !== 1) {
            return false;
        }

        [$user, $domain] = explode('@', $email);
        $valid_ip4_form = '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
        $valid_email_pattern = '^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+(XN\-\-[a-z0-9]{2,20}|[a-z]{2,20}))|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$';

        if (str_starts_with($user, '"') && str_ends_with($user, '"')) {
            $user = trim($user, '"');
            $user = str_replace(' ', '', $user);
            $email = $user . '@' . $domain;
        }

        if (str_contains($domain, ' ')) {
            return false;
        }

        if (preg_match('/' . $valid_ip4_form . '/', $domain)) {
            $digit = explode('.', $domain);
            for ($i = 0; $i < 4; $i++) {
                if ($digit[$i] > 255) {
                    $valid_address = false;
                    return $valid_address;
                }
                if ($digit[0] == 192 || $digit[0] == 10) {
                    $valid_address = false;
                    return $valid_address;
                }
            }
        }

        if (!preg_match('/' . $valid_email_pattern . '/i', $email)) {
            $valid_address = false;
            return $valid_address;
        }

        // @TODO - At this point $valid_address is always true due to early-returns.
        $this->notify('NOTIFY_EMAIL_VALIDATION_TEST', [$email, $valid_address]);

        return $valid_address;
    }

    /**
     * Determine the correct connection credentials for transporting the email.
     */
    protected function setupEmailTransport(string $module, PHPMailer $mail): string
    {
        // ESTABLISH SMTP TRANSPORT/CONNECTION
        $sending_newsletter = false;
        $email_transport = zen_config('EMAIL_TRANSPORT');
        $email_mailbox = zen_config('EMAIL_SMTPAUTH_MAILBOX');
        $email_password = zen_config('EMAIL_SMTPAUTH_PASSWORD');
        $email_mail_server = zen_config('EMAIL_SMTPAUTH_MAIL_SERVER');
        $email_mail_server_port = (int)zen_config('EMAIL_SMTPAUTH_MAIL_SERVER_PORT');
        if ($this->isNewsletter($module)) {
            $sending_newsletter = true;
            $email_transport = 'smtpauth';
            $email_mailbox = zen_config('NEWSLETTER_EMAIL_SMTPAUTH_MAILBOX');
            $email_password = zen_config('NEWSLETTER_EMAIL_SMTPAUTH_PASSWORD');
            $email_mail_server = zen_config('NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER');
            $email_mail_server_port = (int)zen_config('NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER_PORT');
        }

        switch ($email_transport) {
            case 'Gmail':
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->Host = 'smtp.gmail.com';
                $mail->Username = (!empty(trim($email_mailbox))) ? trim($email_mailbox) : zen_config('EMAIL_FROM');
                if (trim($email_password) !== '') {
                    $mail->Password = trim($email_password);
                }
                break;
            case 'smtpauth':
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->Username = (!empty(trim($email_mailbox))) ? trim($email_mailbox) : zen_config('EMAIL_FROM');
                if (trim($email_password) !== '') {
                    $mail->Password = trim($email_password);
                }
                $mail->Host = (trim($email_mail_server) !== '') ? trim($email_mail_server) : 'localhost';
                if ($email_mail_server_port !== 25 && $email_mail_server_port !== 0) {
                    $mail->Port = $email_mail_server_port;
                }
                if ($mail->Port < 30 && $mail->Host === 'smtp.gmail.com') {
                    $mail->Port = 587;
                }
                if ($mail->Port === 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }
                if ($mail->Port === 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }

                if (!$sending_newsletter) {
                    if (defined('SMTPAUTH_EMAIL_PROTOCOL') && SMTPAUTH_EMAIL_PROTOCOL !== 'none') {
                        $mail->SMTPSecure = SMTPAUTH_EMAIL_PROTOCOL;
                    }
                } elseif (defined('NEWSLETTER_SMTPAUTH_EMAIL_PROTOCOL') && NEWSLETTER_SMTPAUTH_EMAIL_PROTOCOL !== 'none') {
                    $mail->SMTPSecure = NEWSLETTER_SMTPAUTH_EMAIL_PROTOCOL;
                }
                break;
            case 'smtp':
                $mail->isSMTP();
                $mail->Host = trim($email_mail_server);
                if ($email_mail_server_port !== 25 && $email_mail_server_port !== 0) {
                    $mail->Port = $email_mail_server_port;
                }
                break;
            case 'PHP':
                $mail->isMail();
                break;
            case 'Qmail':
                $mail->isQmail();
                break;
            case 'sendmail':
            case 'sendmail-f':
            default:
                $mail->isSendmail();
                if (defined('EMAIL_SENDMAIL_PATH') && file_exists(trim(EMAIL_SENDMAIL_PATH))) {
                    $mail->Sendmail = trim(EMAIL_SENDMAIL_PATH);
                }
                break;
        }
        return $email_transport;
    }

    /**
     * Newsletter emails can be configured to use a separate SMTP authentication.
     * (This is usually to allow them to be part of another mailing list server, or to handle different volumes of traffic.)
     */
    protected function isNewsletter(string $module): bool
    {
        if (empty(zen_config('NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER', ''))) {
            return false;
        }
        $newsletters = (string)zen_config('NEWSLETTER_MODULES', '');
        if (empty($newsletters)) {
            return false;
        }
        $newsletter_modules = array_map('trim', explode(',', $newsletters));
        return in_array($module, $newsletter_modules, true);
    }

    /**
     * Attach and embed any images marked with embed="yes" in the HTML.
     */
    private function processEmbeddedImages(string $email_html, PHPMailer $mail): string
    {
        if (!defined('EMAIL_ATTACH_EMBEDDED_IMAGES') || EMAIL_ATTACH_EMBEDDED_IMAGES !== 'Yes') {
            return $email_html;
        }

        $imageFiles = [];
        $imagesToProcess = [];
        if (preg_match_all('#<img.*src=\"(.*?)\".*?\/>#', $email_html, $imagesToProcess)) {
            for ($i = 0, $n = count($imagesToProcess[0]); $i < $n; $i++) {
                $exists = strpos($imagesToProcess[0][$i], 'embed="yes"');
                if ($exists === false) {
                    continue;
                }
                if (array_key_exists($imagesToProcess[1][$i], $imageFiles)) {
                    $substitute = $imageFiles[$imagesToProcess[1][$i]];
                } elseif (file_exists(DIR_FS_CATALOG . $imagesToProcess[1][$i])) {
                    $rpos = strrpos($imagesToProcess[1][$i], '.');
                    $ext = substr($imagesToProcess[1][$i], $rpos + 1);
                    $name = basename($imagesToProcess[1][$i], '.' . $ext);
                    $mimetype = match (strtolower($ext)) {
                        'gif' => 'image/gif',
                        'jpg', 'jpeg' => 'image/jpeg',
                        default => 'image/png',
                    };
                    $substitute = $name . $i;
                    $mail->AddEmbeddedImage(
                        DIR_FS_CATALOG . $imagesToProcess[1][$i],
                        $substitute,
                        $name . '.' . $ext,
                        'base64',
                        $mimetype
                    );
                    $imageFiles[$imagesToProcess[1][$i]] = $substitute;
                } else {
                    $email_html = str_replace($imagesToProcess[1][$i], '', $email_html);
                    continue;
                }
                $email_html = str_replace($imagesToProcess[1][$i], 'cid:' . $substitute, $email_html);
            }
        }
        return $email_html;
    }

    /**
     * Normalize, filter, and attach files to the outgoing mailer instance.
     */
    private function processFileAttachments(array|string $attachments_list, string $module, PHPMailer $mail): void
    {
        global $messageStack;

        if ($attachments_list === '') {
            $attachments_list = [];
        }
        if (is_string($attachments_list)) {
            if (file_exists($attachments_list)) {
                $attachments_list = [['file' => $attachments_list]];
            } elseif (file_exists(DIR_FS_CATALOG . $attachments_list)) {
                $attachments_list = [['file' => DIR_FS_CATALOG . $attachments_list]];
            } else {
                $attachments_list = [];
            }
        }
        global $newAttachmentsList;
        $this->notify('NOTIFY_EMAIL_BEFORE_PROCESS_ATTACHMENTS', ['attachments' => $attachments_list, 'module' => $module], $mail, $attachments_list);
        if (isset($newAttachmentsList) && is_array($newAttachmentsList)) {
            $attachments_list = $newAttachmentsList;
        }
        if (defined('EMAIL_ATTACHMENTS_ENABLED') && EMAIL_ATTACHMENTS_ENABLED && is_array($attachments_list) && $attachments_list !== []) {
            foreach ($attachments_list as $key => $val) {
                $fname = $val['name'] ?? null;
                $mimeType = (!empty($val['mime_type']) && $val['mime_type'] !== 'application/octet-stream') ? $val['mime_type'] : '';
                switch (true) {
                    case (!empty($val['raw_data'])):
                        $fdata = $val['raw_data'];
                        if ($mimeType !== '') {
                            $mail->addStringAttachment($fdata, $fname, 'base64', $mimeType);
                        } else {
                            $mail->addStringAttachment($fdata, $fname);
                        }
                        break;
                    case (isset($val['file']) && file_exists($val['file'])):
                        $fdata = $val['file'];
                        try {
                            if ($mimeType !== '') {
                                $mail->addAttachment($fdata, $fname, 'base64', $mimeType);
                            } else {
                                $mail->addAttachment($fdata, $fname);
                            }
                        } catch (\Exception $exception) {
                            if ($messageStack !== null) {
                                $messageStack->add_session('Error: could not add attachment. ' . $exception->getMessage(), 'error');
                            } else {
                                error_log('Error: could not add attachment. ' . $exception->getMessage());
                            }
                        }
                        break;
                }
            }
        }
        $this->notify('NOTIFY_EMAIL_AFTER_PROCESS_ATTACHMENTS', count($attachments_list));
    }

    /**
     * Select and render an HTML email template for the given module.
     */
    public function buildHtmlFromTemplate(string $module = 'default', array|string $content = ''): string
    {
        global $messageStack, $current_page_base;

        if (null === $current_page_base) {
            $current_page_base = $module;
        }
        $block = [];
        if (is_array($content)) {
            $block = $content;
        } else {
            if ($content === '' || $content === 'none') {
                return '';
            }
            $block['EMAIL_MESSAGE_HTML'] = $content;
        }

        // LOOKUP HTML AND CSS FILES according to current language and page/module

        // English files are assumed to be in the main directory, so we set it to blank. Other languages are assumed to be in a subdirectory.
        $langfolder = strtolower($_SESSION['languages_code']) . '/';

        /**
         * Allow an observer to register additional HTML and CSS template folders to the lookup flow.
         * Registered additional folders will be searched in the order they are registered, using the system /email directory as a fallback.
         *
         * KEY: A plugin should register its own email directory path via an observer class, with 4 simple steps:
         *      1. use the InteractsWithPlugins and ObserverManager traits.
         *      2. In the constructor, attach using $this->attach($this, ['NOTIFY_EMAIL_REGISTER_ADDITIONAL_TEMPLATE_DIRS']);
         *         and initialize path detection using $this->detectZcPluginDetails(__DIR__);
         *      3. Then create a function to listen for the NOTIFY_EMAIL_REGISTER_ADDITIONAL_TEMPLATE_DIRS event:
         *         public function notify_email_register_additional_template_dirs(&$class, $eventID, $params, &$extra_email_template_paths) {
         *             $extra_email_template_paths[] = $this->zcPluginPath . 'email';
         *         }
         *      4. And then your custom email template files should go in that /email directory (ie: zc_plugins/plugin-name/version/email)
         */
        $extra_email_template_paths = [];
        $this->notify('NOTIFY_EMAIL_REGISTER_ADDITIONAL_TEMPLATE_DIRS', ['module' => $module, 'langfolder' => $langfolder, 'content' => $content], $extra_email_template_paths);

        $rootsToCheck = [DIR_FS_EMAIL_TEMPLATES];
        // Add LFI-safe paths to the beginning of the list.
        $rootsToCheck = array_merge(
            array_filter(array_map(static function ($val) {
                return str_starts_with($val, DIR_FS_CATALOG) ? (rtrim($val, '/') . '/') : null;
            }, $extra_email_template_paths)),
            $rootsToCheck
        );

        $block['EMAIL_COMMON_CSS'] = '';

        // FIND CSS FILE
        $patternsToTest = [
            $langfolder . 'email_common.css',
            'email_common.css',
        ];
        $found = false;
        foreach ($rootsToCheck as $root) {
            foreach ($patternsToTest as $pattern) {
                if (empty($pattern)) {
                    continue;
                }
                $path = $root . $pattern;
                if (file_exists($path)) {
                    $contents = file_get_contents($path);
                    if ($contents === false) {
                        error_log(sprintf('ERROR: The email css file (%s) was found but cannot be read.', $path));
                        continue;
                    }
                    $block['EMAIL_COMMON_CSS'] = $contents;
                    $found = true;
                    break 2;
                }
            }
        }
        if (!$found) {
            trigger_error('Email Template Warning: Unable to locate email_common.css file for use in HTML email templates. Searched for: '
                . implode(', ', $patternsToTest) . ' in: ' . implode(', ', $rootsToCheck), E_USER_WARNING);
        }

        // FIND HTML TEMPLATE FILE
        $patternsToTest = [
            $langfolder . 'email_template_' . str_replace(['_extra', '_admin'], '', $module) . '.html',
            'email_template_' . str_replace(['_extra', '_admin'], '', $module) . '.html',
            $langfolder . 'email_template_' . $current_page_base . '.html',
            'email_template_' . $current_page_base . '.html',
            (!empty($block['EMAIL_TEMPLATE_FILENAME']) ? $block['EMAIL_TEMPLATE_FILENAME'] . '.html' : null),
            $langfolder . 'email_template_default.html',
            'email_template_default.html',
        ];

        $found = false;
        foreach ($rootsToCheck as $root) {
            foreach ($patternsToTest as $pattern) {
                if (empty($pattern)) {
                    continue;
                }
                $template_filename = $root . $pattern;
                if (file_exists($template_filename)) {
                    $template_content = file_get_contents($template_filename);
                    if ($template_content === false) {
                        error_log(sprintf('ERROR: The email template for (%s) cannot be opened.', $template_filename));
                        continue;
                    }
                    $found = true;
                    break 2;
                }
            }
        }
        if (false === $found) {
            if (isset($messageStack)) {
                $messageStack->add('header', sprintf('ERROR: The email template for (%s) or (%s) cannot be found.', $module, $current_page_base), 'caution');
            }
            return '';
        }

        // REMOVE PARTS THAT NEED TO BE CLEANED
        $template_content = str_replace("\t", ' ', $template_content);

        if (empty($block['EXTRA_INFO']) || empty(trim($block['EXTRA_INFO']))) {
            $template_content = preg_replace('/<div class="extra-info">\s?\$EXTRA_INFO\s?<\/div>/', '', $template_content);
        }

        /**
         * Put all relevant variable content into the template.
         */
        $block = $this->setTemplateVarsFromDefines($block, $module);
        foreach ($block as $key => $value) {
            $template_content = str_replace('$' . $key, (string)$value, $template_content);
        }

        if (defined('EMAIL_SYSTEM_DEBUG') && EMAIL_SYSTEM_DEBUG === 'preview') {
            echo $template_content;
        }

        return $template_content;
    }

    /**
     * Ensure the common template vars are set from defines/constants/lookups.
     */
    protected function setTemplateVarsFromDefines(array $block, string $module): array
    {
        if (empty($block['EMAIL_LOGO_FILE'])) {
            $domain = (IS_ADMIN_FLAG === true) ? HTTP_CATALOG_SERVER : HTTP_SERVER;
            $block['EMAIL_LOGO_FILE'] = $domain . DIR_WS_CATALOG . 'email/' . EMAIL_LOGO_FILENAME;
        }
        if (empty($block['EMAIL_LOGO_ALT_TEXT'])) {
            $block['EMAIL_LOGO_ALT_TEXT'] = EMAIL_LOGO_ALT_TEXT;
        }
        if (empty($block['EMAIL_LOGO_ALT_TITLE_TEXT'])) {
            $block['EMAIL_LOGO_ALT_TITLE_TEXT'] = EMAIL_LOGO_ALT_TITLE_TEXT;
        }
        if (empty($block['EMAIL_LOGO_WIDTH'])) {
            $block['EMAIL_LOGO_WIDTH'] = EMAIL_LOGO_WIDTH;
        }
        if (empty($block['EMAIL_LOGO_HEIGHT'])) {
            $block['EMAIL_LOGO_HEIGHT'] = EMAIL_LOGO_HEIGHT;
        }

        zen_define_default('EMAIL_EXTRA_HEADER_INFO', '');
        if (empty($block['EXTRA_HEADER_INFO'])) {
            $block['EXTRA_HEADER_INFO'] = EMAIL_EXTRA_HEADER_INFO;
        }

        if (!defined('HTTP_CATALOG_SERVER')) {
            define('HTTP_CATALOG_SERVER', HTTP_SERVER);
        }
        if (empty($block['EMAIL_STORE_NAME'])) {
            $block['EMAIL_STORE_NAME'] = zen_config('STORE_NAME');
        }
        if (empty($block['EMAIL_STORE_URL'])) {
            $block['EMAIL_STORE_URL'] = '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . '">' . zen_config('STORE_NAME') . '</a>';
        }
        if (empty($block['EMAIL_STORE_OWNER'])) {
            $block['EMAIL_STORE_OWNER'] = zen_config('STORE_OWNER');
        }
        if (empty($block['EMAIL_FOOTER_COPYRIGHT'])) {
            $block['EMAIL_FOOTER_COPYRIGHT'] = EMAIL_FOOTER_COPYRIGHT;
        }
        if (empty($block['EMAIL_DISCLAIMER'])) {
            $block['EMAIL_DISCLAIMER'] = sprintf(EMAIL_DISCLAIMER, '<a href="mailto:' . zen_config('STORE_OWNER_EMAIL_ADDRESS') . '">' . zen_config('STORE_OWNER_EMAIL_ADDRESS') . '</a>');
        }
        if (empty($block['EMAIL_SPAM_DISCLAIMER'])) {
            $block['EMAIL_SPAM_DISCLAIMER'] = EMAIL_SPAM_DISCLAIMER;
        }
        if (empty($block['EMAIL_DATE_SHORT'])) {
            $block['EMAIL_DATE_SHORT'] = zen_date_short(date('Y-m-d H:i:s'));
        }
        if (empty($block['EMAIL_DATE_LONG'])) {
            $block['EMAIL_DATE_LONG'] = zen_date_long(date('Y-m-d H:i:s'));
        }
        if (empty($block['BASE_HREF'])) {
            $block['BASE_HREF'] = HTTP_CATALOG_SERVER . DIR_WS_CATALOG;
        }
        if (empty($block['CHARSET'])) {
            $block['CHARSET'] = CHARSET;
        }

        if (!isset($block['EXTRA_INFO'])) {
            $block['EXTRA_INFO'] = '';
        }
        if (!str_ends_with($module, '_extra') && $module !== 'contact_us' && $module !== 'ask_a_question') {
            $block['EXTRA_INFO'] = '';
        }

        $block['COUPON_BLOCK'] = '';
        if (!empty($block['COUPON_TEXT_VOUCHER_IS']) && !empty($block['COUPON_TEXT_TO_REDEEM'])) {
            $block['COUPON_BLOCK'] =
                '<div class="coupon-block">' .
                $block['COUPON_TEXT_VOUCHER_IS'] . $block['COUPON_DESCRIPTION'] .
                '<br>' .
                $block['COUPON_TEXT_TO_REDEEM'] .
                '<span class="coupon-code">' . $block['COUPON_CODE'] . '</span>' .
                '</div>';
        }

        $block['GV_BLOCK'] = '';
        if (!empty($block['GV_ANNOUNCE']) && !empty($block['GV_REDEEM'])) {
            $block['GV_BLOCK'] = '<div class="gv-block">' . $block['GV_ANNOUNCE'] . '<br>' . $block['GV_REDEEM'] . '</div>';
        }

        if (IS_ADMIN_FLAG === true) {
            $block['UNSUBSCRIBE_LINK'] = str_replace("\n", '', TEXT_UNSUBSCRIBE) . ' <a href="' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
        } else {
            $block['UNSUBSCRIBE_LINK'] = str_replace("\n", '', TEXT_UNSUBSCRIBE) . ' <a href="' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
        }
        return $block;
    }

    /**
     * Build an array of additional email content collected and sent on admin-copies of emails.
     */
    public static function collectExtraInfo(string $from, string $email_from, string $login, string $login_email, string $login_phone = '', string $login_fax = '', array $moreinfo = []): array
    {
        $email_host_address = '';
        if (empty($_SESSION['customers_host_address'])) {
            if (zen_config('SESSION_IP_TO_HOST_ADDRESS') === 'true' && !empty(trim($_SERVER['REMOTE_ADDR'], '.'))) {
                $email_host_address = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            }
        } else {
            $email_host_address = $_SESSION['customers_host_address'];
        }

        $extra_info = [];
        $extra_info['TEXT'] =
            OFFICE_USE . "\t\n" .
            OFFICE_FROM . "\t" . $from . "\n" .
            OFFICE_EMAIL . "\t" . $email_from . "\n" .
            (trim($login) !== '' ? OFFICE_LOGIN_NAME . "\t" . $login . "\n" : '') .
            (trim($login_email) !== '' ? OFFICE_LOGIN_EMAIL . "\t" . $login_email . "\n" : '') .
            (trim($login_phone) !== '' ? OFFICE_LOGIN_PHONE . "\t" . zen_output_string_protected($login_phone) . "\n" : '') .
            ($login_fax !== '' ? OFFICE_LOGIN_FAX . "\t" . zen_output_string_protected($login_fax) . "\n" : '') .
            OFFICE_IP_ADDRESS . "\t" . $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'] . "\n" .
            ($email_host_address != '' ? OFFICE_HOST_ADDRESS . "\t" . $email_host_address . "\n" : '') .
            OFFICE_DATE_TIME . "\t" . date("D M j Y G:i:s T") . "\n";

        $extra_info['HTML'] =
            '<table class="extra-info">' .
            '<tr><td class="extra-info-bold" colspan="2">' . OFFICE_USE . '</td></tr>' .
            '<tr><td class="extra-info-bold">' . OFFICE_FROM . '</td><td>' . $from . '</td></tr>' .
            '<tr><td class="extra-info-bold">' . OFFICE_EMAIL . '</td><td>' . $email_from . '</td></tr>' .
            ($login !== '' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_NAME . '</td><td>' . $login . '</td></tr>' : '') .
            ($login_email !== '' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_EMAIL . '</td><td>' . $login_email . '</td></tr>' : '') .
            ($login_phone !== '' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_PHONE . '</td><td>' . zen_output_string_protected($login_phone) . '</td></tr>' : '') .
            ($login_fax !== '' ? '<tr><td class="extra-info-bold">' . OFFICE_LOGIN_FAX . '</td><td>' . zen_output_string_protected($login_fax) . '</td></tr>' : '') .
            '   <tr><td class="extra-info-bold">' . OFFICE_IP_ADDRESS . '</td><td>' . $_SESSION['customers_ip_address'] . ' - ' . $_SERVER['REMOTE_ADDR'] . '</td></tr>' .
            ($email_host_address != '' ? '<tr><td class="extra-info-bold">' . OFFICE_HOST_ADDRESS . '</td><td>' . $email_host_address . '</td></tr>' : '') .
            '   <tr><td class="extra-info-bold">' . OFFICE_DATE_TIME . '</td><td>' . date('D M j Y G:i:s T') . '</td></tr>';

        foreach ($moreinfo as $key => $val) {
            $extra_info['TEXT'] .= zen_output_string_protected($key) . ": \t" . zen_output_string_protected($val) . "\n";
            $extra_info['HTML'] .= '<tr><td class="extra-info-bold">' . zen_output_string_protected($key) . '</td><td>' . zen_output_string_protected($val) . '</td></tr>';
        }

        $extra_info['TEXT'] .= "\n\n";
        $extra_info['HTML'] .= "</table>\n";

        return $extra_info;
    }

    /**
     * Store a sent email to the archive log table.
     */
    private function archiveWrite(string $to_name, string $to_email_address, string $from_email_name, string $from_email_address, string $email_subject, string $email_html, string $email_text, string $module, string $error_msgs): void
    {
        global $db;
        $this->notify('NOTIFY_EMAIL_BEGIN_ARCHIVE_WRITE', [$to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module, $error_msgs]);
        $to_name = zen_db_prepare_input($to_name);
        $to_email_address = zen_db_prepare_input($to_email_address);
        $from_email_name = zen_db_prepare_input($from_email_name);
        $from_email_address = zen_db_prepare_input($from_email_address);
        $email_subject = zen_db_prepare_input($email_subject);
        $email_html = (zen_config('EMAIL_USE_HTML') === 'true') ? $this->dbPrepareInputHtmlSafe($email_html) : 'HTML disabled in admin';
        $email_text = zen_db_prepare_input($email_text);
        $module = zen_db_prepare_input($module);
        $error_msgs = empty($error_msgs) ? 'NULL' : zen_db_prepare_input($error_msgs);

        $db->perform(TABLE_EMAIL_ARCHIVE, [
            [ 'fieldName' => 'email_to_name', 'value' => $to_name, 'type' => 'string' ],
            [ 'fieldName' => 'email_to_address', 'value' => $to_email_address, 'type' => 'string' ],
            [ 'fieldName' => 'email_from_name', 'value' => $from_email_name, 'type' => 'string' ],
            [ 'fieldName' => 'email_from_address', 'value' => $from_email_address, 'type' => 'string' ],
            [ 'fieldName' => 'email_subject', 'value' => $email_subject, 'type' => 'string' ],
            [ 'fieldName' => 'email_html', 'value' => $email_html, 'type' => 'string' ],
            [ 'fieldName' => 'email_text', 'value' => $email_text, 'type' => 'string' ],
            [ 'fieldName' => 'date_sent', 'value' => 'now()', 'type' => 'passthru' ],
            [ 'fieldName' => 'module', 'value' => $module, 'type' => 'string' ],
            [ 'fieldName' => 'errorinfo', 'value' => $error_msgs, 'type' => 'string' ],
        ]);
    }

    /**
     * Prepare a string for DB input without escaping HTML entities (used for archiving HTML email bodies).
     */
    private function dbPrepareInputHtmlSafe(string|array $string): string|array
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

    /**
     * Get customer email address by customer ID.
     * NOTE: For the current customer, it is better to retrieve this from the in-memory Customer object.
     */
    public static function getAddressFromCustomerId(int|string $customers_id): string
    {
        global $db;
        $result = $db->Execute("SELECT customers_email_address
                                        FROM " . TABLE_CUSTOMERS . "
                                        WHERE customers_id = " . (int)$customers_id);
        if ($result->EOF) {
            return '';
        }
        return $result->fields['customers_email_address'];
    }
}
