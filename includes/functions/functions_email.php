<?php
    /**
     * functions_email.php
     * Processes all outbound email from Zen Cart
     * Hooks into phpMailer class for actual email encoding and sending
     *
     * @copyright Copyright 2003-2024 Zen Cart Development Team
     * @copyright Portions Copyright 2003 osCommerce
     * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
     * @version $Id: neekfenwick 2024 May 22 Modified in v2.1.0-alpha1 $
     */

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

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
     * If you need to force an authentication protocol, enter appropriate option here: 'ssl' or 'tls'
     * Note that selecting a gmail server or port 465 will automatically select 'ssl' for you.
     */
    zen_define_default('SMTPAUTH_EMAIL_PROTOCOL', 'none');

    /**
     * Send email. This is the central mail function.
     * If using "PHP" transport method, the SMTP Server or other mail application should be configured correctly in server's php.ini
     *
     * @param string $to_name The name of the recipient, e.g. "Jim Johanssen"
     * @param string $to_address The email address of the recipient, e.g. john.smith@hzq.com
     * @param string $email_subject The subject of the email
     * @param string $email_text The text of the email, may contain HTML entities
     * @param string $from_email_name The name of the sender, e.g. Shop Administration
     * @param string $from_email_address The email address of the sender, e.g. info@myzenshop.com
     * @param array $block Array containing values to be inserted into HTML-based email template
     * @param string $module The module name of the routine calling zen_mail. Used for HTML template selection and email archiving.
     *                                  This is passed to the archive function denoting what module initiated the sending of the email
     * @param array $attachments_list Array of attachment names/mime-types to be included  (this portion still in testing, and not fully reliable)
     * @param string $email_reply_to_name Name of the "reply-to" header (defaults to store name if not specified, except for contact-us and order-confirmation)
     * @param string $email_reply_to_address Email address for reply-to header (defaults to store email address if not specified, except for contact-us and order-confirmation)
     **/
    function zen_mail($to_name, $to_address, $email_subject, $email_text, $from_email_name, $from_email_address, $block = [], $module = 'default', $attachments_list = '', $email_reply_to_name = '', $email_reply_to_address = '')
    {
        global $db, $messageStack, $zco_notifier;
        if (SEND_EMAILS !== 'true') {
            return false;
        }  // if sending email is disabled in Admin, just exit

        if (defined('DEVELOPER_OVERRIDE_EMAIL_STATUS') && DEVELOPER_OVERRIDE_EMAIL_STATUS === 'false') {
            return false;
        }  // disable email sending when in developer mode

        // ignore sending emails for any of the following pages
        // (The EMAIL_MODULES_TO_SKIP constant can be defined in a new file in the "extra_configures" folder)
        if (defined('EMAIL_MODULES_TO_SKIP') && in_array($module, explode(',', constant('EMAIL_MODULES_TO_SKIP')))) {
            return false;
        }

        // check for injection attempts. If new-line characters found in header fields, simply fail to send the message
        foreach ([$from_email_address, $to_address, $from_email_name, $to_name, $email_subject] as $key => $value) {
            if (strpos($value, "\r") !== false || strpos($value, "\n") !== false) {
                return false;
            }
        }

        // if no text or html-msg supplied, exit
        if (trim($email_text) === '' && empty($block['EMAIL_MESSAGE_HTML'])) {
            return false;
        }

        // Parse "from" addresses for "name" <email@address.com> structure, and supply name/address info from it.
        if (preg_match("/ *([^<]*) *<([^>]*)> */i", $from_email_address, $regs)) {
            $from_email_name = trim($regs[1]);
            $from_email_address = $regs[2];
        }
        // if email name is empty or the same as email address, use the Store Name as the senders 'Name'
        if (empty($from_email_name) || $from_email_name === $from_email_address) {
            $from_email_name = STORE_NAME;
        }

        // loop thru multiple email recipients if more than one listed  --- (esp for the admin's "Extra" emails)...
        foreach (explode(',', $to_address) as $key => $value) {
            if (preg_match("/ *([^<]*) *<([^>]*)> */i", $value, $regs)) {
                $to_name = str_replace('"', '', trim($regs[1]));
                $to_email_address = $regs[2];
            } elseif (preg_match("/ *([^ ]*) */i", $value, $regs)) {
                $to_email_address = trim($regs[1]);
            }
            if (!isset($to_email_address)) {
                $to_email_address = trim($to_address);
            } //if not more than one, just use the main one.

            $zco_notifier->notify('NOTIFY_EMAIL_ADDRESS_TEST', [], $to_name, $to_email_address, $email_subject);
            // ensure the address is valid, to prevent unnecessary delivery failures
            if (!zen_validate_email($to_email_address)) {
                $zco_notifier->notify('NOTIFY_EMAIL_ADDRESS_VALIDATION_FAILURE', sprintf(EMAIL_SEND_FAILED . ' (failed validation)', $to_name, $to_email_address, $email_subject));
                error_log(sprintf(EMAIL_SEND_FAILED . ' (failed validation)', $to_name, $to_email_address, $email_subject));
                continue;
            }

            //define some additional html message blocks available to templates, then build the html portion.
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
            $email_html = (!is_array($block) && substr($block, 0, 6) == '<html>') ? $block : zen_build_html_email_from_template($module, $block);
            if (!is_array($block) && ($block === '' || $block === 'none')) {
                $email_html = '';
            }

            // Build the email based on whether customer has selected HTML or TEXT, and whether we have supplied HTML or TEXT-only components
            // special handling for XML content
            if ($email_text === '') {
                $email_text = str_replace(
                    [
                        '<br>',
                        '<br />',
                        '</p>',
                    ],
                    [
                        "<br>\n",
                        "<br>\n",
                        "</p>\n",
                    ],
                    $block['EMAIL_MESSAGE_HTML']
                );
                $email_text = ($module !== 'xml_record') ? zen_output_string_protected(stripslashes(strip_tags($email_text))) : $email_text;
            } elseif ($module !== 'xml_record') {
                $email_text = preg_replace('~</?([^(strong>|br ?\/?>|a href=|p |span|script|li|ol|ul|em|b>|i>|u>)])~', '@lt@\\1', $email_text);
                $email_text = strip_tags($email_text);
                $email_text = str_replace('@lt@', '<', $email_text);
            }

            if (zen_is_non_transactional_email($module)) {
                if (defined('EMAIL_DISCLAIMER') && EMAIL_DISCLAIMER !== '' && !strstr($email_text, sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS)) && $to_email_address !== STORE_OWNER_EMAIL_ADDRESS && !defined('EMAIL_DISCLAIMER_NEW_CUSTOMER')) {
                    $email_text .= "\n" . sprintf(EMAIL_DISCLAIMER, STORE_OWNER_EMAIL_ADDRESS);
                }
                if (defined('EMAIL_SPAM_DISCLAIMER') && EMAIL_SPAM_DISCLAIMER !== '' && !strstr($email_text, EMAIL_SPAM_DISCLAIMER) && $to_email_address !== STORE_OWNER_EMAIL_ADDRESS) {
                    $email_text .= "\n\n" . EMAIL_SPAM_DISCLAIMER;
                }
            }

            // bof: body of the email clean-up
            // clean up &amp; and && from email text
            $email_text = preg_replace('/(&amp;)+/', '&amp;', $email_text);
            $email_text = preg_replace('/(&amp;)+/', '&', $email_text);
            $email_text = preg_replace('/&{2,}/', '&', $email_text);

            // clean up currencies for text emails
            if (defined('CURRENCIES_TRANSLATIONS') && !empty(CURRENCIES_TRANSLATIONS)) {
                $zen_fix_currencies = preg_split("/[:,]/", str_replace(' ', '', CURRENCIES_TRANSLATIONS));
                $size = count($zen_fix_currencies);
                for ($i = 0, $n = $size; $i < $n; $i += 2) {
                    if (empty($zen_fix_currencies[$i + 1])) {
                        break;
                    }
                    $zen_fix_current = $zen_fix_currencies[$i];
                    $zen_fix_replace = $zen_fix_currencies[$i + 1];
                    if (strlen($zen_fix_current) !== 0) {
                        while (strpos($email_text, $zen_fix_current)) {
                            $email_text = str_replace($zen_fix_current, $zen_fix_replace, $email_text);
                        }
                    }
                }
            }

            // fix double quotes
            $email_text = preg_replace('/(&quot;)+/', '"', $email_text);
            // fix symbols
            $email_text = preg_replace('/(&lt;)+/', '<', $email_text);
            $email_text = preg_replace('/(&gt;)+/', '>', $email_text);
            // prevent null characters
            $email_text = preg_replace('/\0+/', ' ', $email_text);

            // fix slashes
            $text = stripslashes($email_text);
            $email_html = stripslashes($email_html);

            // eof: body of the email clean-up

            //determine customer's email preference type: HTML or TEXT-ONLY  (HTML assumed if not specified)
            $sql = "SELECT customers_email_format FROM " . TABLE_CUSTOMERS . " WHERE customers_email_address= :custEmailAddress:";
            $sql = $db->bindVars($sql, ':custEmailAddress:', $to_email_address, 'string');
            $result = $db->Execute($sql);
            $customers_email_format = ($result->RecordCount() > 0) ? $result->fields['customers_email_format'] : '';

            /**
             * Valid formats:
             * HTML - if HTML content has been provided/prepared, it will be used. EMAIL_USE_HTML must be set to true in configs
             * TEXT - a text-only version of the email will be sent, and the HTML version ignored
             * NONE or OUT - implies opt-out, ie: send no emails, so aborts sending
             */
            $zco_notifier->notify('NOTIFY_EMAIL_DETERMINING_EMAIL_FORMAT', $to_email_address, $customers_email_format, $module);

            if ($customers_email_format === 'NONE' || $customers_email_format === 'OUT') {
                continue;
            } //if requested no mail, then don't send, but continue processing others.

            // handling admin/"extra"/copy emails:
            if (ADMIN_EXTRA_EMAIL_FORMAT === 'TEXT' && substr($module, -6) == '_extra') {
                $email_html = '';  // just blank out the html portion if admin has selected text-only
            }
            //determine what format to send messages in if this is an admin email for newsletters:
            if ($customers_email_format === '' && ADMIN_EXTRA_EMAIL_FORMAT === 'HTML' && in_array($module, ['newsletters', 'product_notification']) && isset($_SESSION['admin_id'])) {
                $customers_email_format = 'HTML';
            }

            // special handling for XML content
            if ($module === 'xml_record') {
                $email_html = '';
                $customers_email_format = 'TEXT';
            }

            //notifier intercept option
            $zco_notifier->notify('NOTIFY_EMAIL_AFTER_EMAIL_FORMAT_DETERMINED');

            // Create a new mail object with the phpmailer class
            $mail = new PHPMailer();
            $mail->XMailer = 'Self-Hosted Zen Cart merchant';
            $lang_code = strtolower(($_SESSION['languages_code'] === '' ? 'en' : $_SESSION['languages_code']));
            $mail->SetLanguage($lang_code);
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

            $sending_newsletter = false;
            $email_transport = EMAIL_TRANSPORT;
            $email_mailbox = EMAIL_SMTPAUTH_MAILBOX;
            $email_password = EMAIL_SMTPAUTH_PASSWORD;
            $email_mail_server = EMAIL_SMTPAUTH_MAIL_SERVER;
            $email_mail_server_port = (int)EMAIL_SMTPAUTH_MAIL_SERVER_PORT;
            if (defined('NEWSLETTER_MODULES') && !empty(NEWSLETTER_MODULES) && defined('NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER') && !empty(NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER)) {
                $modules = explode(',', str_replace(' ', '', NEWSLETTER_MODULES));
                if (in_array($module, $modules)) {
                    $sending_newsletter = true;
                    $email_transport = 'smtpauth';
                    $email_mailbox = NEWSLETTER_EMAIL_SMTPAUTH_MAILBOX;
                    $email_password = NEWSLETTER_EMAIL_SMTPAUTH_PASSWORD;
                    $email_mail_server = NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER;
                    $email_mail_server_port = (int)NEWSLETTER_EMAIL_SMTPAUTH_MAIL_SERVER_PORT;
                }
            }

            switch ($email_transport) {
                case ('Gmail'):
                    $mail->isSMTP();
                    $mail->SMTPAuth = true;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->Host = 'smtp.gmail.com';
                    $mail->Username = (!empty(trim($email_mailbox))) ? trim($email_mailbox) : EMAIL_FROM;
                    if (trim($email_password) !== '') {
                        $mail->Password = trim($email_password);
                    }
                    break;
                case 'smtpauth':
                    $mail->isSMTP();
                    $mail->SMTPAuth = true;
                    $mail->Username = (!empty(trim($email_mailbox))) ? trim($email_mailbox) : EMAIL_FROM;
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
                    //set encryption protocol to allow support for secured email protocols
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

            $mail->Subject = $email_subject;

            if ($email_transport === 'sendmail-f' || EMAIL_SEND_MUST_BE_STORE === 'Yes') {
                $mail->Sender = EMAIL_FROM;
            }

            // set the reply-to address.  If none set yet, then use Store's default email name/address.
            // If sending from checkout or contact-us, use the supplied info
            $email_reply_to_address = (!empty($email_reply_to_address)) ? $email_reply_to_address : (in_array($module, ['contact_us', 'ask_a_question', 'checkout_extra']) ? $from_email_address : EMAIL_FROM);
            $email_reply_to_name = (!empty($email_reply_to_name)) ? $email_reply_to_name : (in_array($module, ['contact_us', 'ask_a_question', 'checkout_extra']) ? $from_email_name : STORE_NAME);
            $mail->addReplyTo($email_reply_to_address, $email_reply_to_name);

            $mail->setFrom($from_email_address, $from_email_name);
            // if mailserver requires that all outgoing mail must go "from" an email address matching domain on server, set it to store address
            if (EMAIL_SEND_MUST_BE_STORE === 'Yes') {
                $mail->From = EMAIL_FROM;
            }
            // override to developer email address if set
            if (defined('DEVELOPER_OVERRIDE_EMAIL_ADDRESS') && DEVELOPER_OVERRIDE_EMAIL_ADDRESS !== '') {
                $to_email_address = DEVELOPER_OVERRIDE_EMAIL_ADDRESS;
                // ensure the address is valid, to prevent unnecessary delivery failures
                if (!zen_validate_email($to_email_address)) {
                    error_log(sprintf(EMAIL_SEND_FAILED . ' (devEmail failed validation)', $to_name, $to_email_address, $email_subject));
                    continue;
                }
            }

            $mail->addAddress($to_email_address, $to_name);
            //$mail->addAddress($to_email_address);    // (alternate format if no name, since name is optional)
            //$mail->addBCC(STORE_OWNER_EMAIL_ADDRESS, STORE_NAME);
            //$mail->addCC(email_address);

            if (EMAIL_USE_HTML === 'true') {
                $email_html = processEmbeddedImages($email_html, $mail);
            }

            // PROCESS FILE ATTACHMENTS
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
            $zco_notifier->notify('NOTIFY_EMAIL_BEFORE_PROCESS_ATTACHMENTS', ['attachments' => $attachments_list, 'module' => $module], $mail, $attachments_list);
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
                        case (isset($val['file']) && file_exists($val['file'])): //'file' portion must contain the full path to the file to be attached
                            $fdata = $val['file'];
                            try {
                                if ($mimeType !== '') {
                                    $mail->addAttachment($fdata, $fname, 'base64', $mimeType);
                                } else {
                                    $mail->addAttachment($fdata, $fname);
                                }
                            } catch (\Exception $exception) {
                                $messageStack->add_session('Error: could not add attachment. ' . $exception->getMessage(), 'error');
                            }
                            break;
                    } // end switch
                } //end foreach attachments_list
            } //endif attachments_enabled
            $zco_notifier->notify('NOTIFY_EMAIL_AFTER_PROCESS_ATTACHMENTS', count($attachments_list));

            // prepare content sections:
            if (EMAIL_USE_HTML === 'true' && trim($email_html) !== '' &&
                ($customers_email_format === 'HTML' || (ADMIN_EXTRA_EMAIL_FORMAT !== 'TEXT' && substr($module, -6) == '_extra'))) {
                // Prepare HTML message
                $mail->msgHTML($email_html);
                if ($text !== '') {
                    // apply the supplied text-only portion instead of the auto-generated portion
                    $mail->AltBody = $text;
                }
            } else {
                // If we got here, then other rules specified to send a text-only message instead of HTML
                $mail->Body = $text;
            }

            // Treat marketing notices as bulk
            if (in_array($module, ['newsletters', 'product_notification'])) {
                $mail->addCustomHeader('Precedence: bulk');
            }

            $mail->addCustomHeader('Auto-Submitted: auto-generated');

            $oldVars = [];
            $tmpVars = ['REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'PHP_SELF', $mail->Mailer === 'smtp' ? null : 'SERVER_NAME'];
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

            $ErrorInfo = '';

            // set Hostname, since it can aid in delivery of emails.
            $defaultHostname = preg_replace('~(^https?://|\/.*$)~', '', defined('HTTP_CATALOG_SERVER') ? HTTP_CATALOG_SERVER : HTTP_SERVER);
            // If emails are being rejected, comment out the following line and try again:
            $mail->Hostname = defined('EMAIL_HOSTNAME') ? EMAIL_HOSTNAME : $defaultHostname;

            $zco_notifier->notify('NOTIFY_EMAIL_READY_TO_SEND', [$mail], $mail);
            /**
             * Send the email. If an error occurs, trap it and display it in the messageStack
             */
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
            $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND');
            foreach ($oldVars as $key => $val) {
                $_SERVER[$key] = $val;
            }

            $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND_WITH_ALL_PARAMS', [$to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module, $ErrorInfo]);
            // Archive this message to storage log
            // don't archive pwd-resets and CC numbers
            if (EMAIL_ARCHIVE === 'true' && $module !== 'password_forgotten_admin' && $module !== 'cc_middle_digs' && $module !== 'no_archive') {
                zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $text, $module, $ErrorInfo);
            } // endif archiving

            // -----
            // If a mail-related error (reported by PHPMailer) occurred, treat the 'recipients_failed' message as a special
            // case, logging to a differently-named log file to make finding these issues easier.  Otherwise, log a PHP notice.
            //
            if ($ErrorInfo !== '') {
                $mail_langs = $mail->getTranslations();
                if (strpos($ErrorInfo, $mail_langs['recipients_failed']) === false) {
                   // Don't log SMTP rejected spam; log others
                   if (!str_contains($ErrorInfo, 'spam content')) {
                       trigger_error('Email Error: ' . $ErrorInfo);
                   }
                } else {
                    $log_prefix = (IS_ADMIN_FLAG === true) ? '/myDEBUG-bounced-email-adm-' : '/myDEBUG-bounced-email-';
                    $log_date = new DateTime();
                    error_log('Request URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL . PHP_EOL . $ErrorInfo, 3, DIR_FS_LOGS . $log_prefix . $log_date->format('Ymd-His-u') . '.log');
                }
            }
        } // end foreach loop thru possible multiple email addresses

        $zco_notifier->notify('NOTIFY_EMAIL_AFTER_SEND_ALL_SPECIFIED_ADDRESSES');

        return isset($ErrorInfo) ? nl2br($ErrorInfo) : '';
    }  // end function

    /**
     * zen_mail_archive_write()
     *
     * this function stores sent emails into a table in the database as a log record of email activity.  This table CAN get VERY big!
     * To disable this function, set the "Email Archives" switch to 'false' in ADMIN!
     *
     * See zen_mail() function description for more details on the meaning of these parameters
     * @param string $to_name
     * @param string $to_email_address
     * @param string $from_email_name
     * @param string $from_email_address
     * @param string $email_subject
     * @param string $email_html
     * @param array $email_text
     * @param string $module
     **/
    function zen_mail_archive_write($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module, $error_msgs)
    {
        global $db, $zco_notifier;
        $zco_notifier->notify('NOTIFY_EMAIL_BEGIN_ARCHIVE_WRITE', [$to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $email_html, $email_text, $module, $error_msgs]);
        $to_name = zen_db_prepare_input($to_name);
        $to_email_address = zen_db_prepare_input($to_email_address);
        $from_email_name = zen_db_prepare_input($from_email_name);
        $from_email_address = zen_db_prepare_input($from_email_address);
        $email_subject = zen_db_prepare_input($email_subject);
        $email_html = (EMAIL_USE_HTML == 'true') ? zen_db_prepare_input_html_safe($email_html) : zen_db_prepare_input('HTML disabled in admin');
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

        return $db;
    }

    /**
     * Transactional emails don't need overly verbose disclaimers, etc
     * However, the following email types are marketing-related or first-time-interaction with recipient, so should probably have disclaimers
     * @param string $email_module_name
     * @return bool
     */
    function zen_is_non_transactional_email($email_module_name)
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

    /////////////////////////////////////////////////////////////////////////////////////////
    ////////END SECTION FOR EMAIL FUNCTIONS//////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * select email template based on 'module' (supplied as param to function)
     * selectively go thru each template tag and substitute appropriate text
     * finally, build full html content as "return" output from class
     **/
    function zen_build_html_email_from_template($module = 'default', $content = '')
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

        // Identify and Read the template file for the type of message being sent
        $langfolder = (strtolower($_SESSION['languages_code']) === 'en') ? '' : (strtolower($_SESSION['languages_code']) . '/');

        // Handle CSS
        $block['EMAIL_COMMON_CSS'] = '';
        $filesToTest = [
            DIR_FS_EMAIL_TEMPLATES . $langfolder . 'email_common.css',
            DIR_FS_EMAIL_TEMPLATES . 'email_common.css',
        ];
        $found = false;
        foreach ($filesToTest as $val) {
            if (file_exists($val)) {
                $block['EMAIL_COMMON_CSS'] = file_get_contents($val);
                $found = true;
                break;
            }
        }
        if (false === $found) {
            trigger_error('Missing common email CSS file: ' . DIR_FS_EMAIL_TEMPLATES . 'email_common.css', E_USER_WARNING);
        }

        // Handle logo image
        if (empty($block['EMAIL_LOGO_FILE'])) {
            $domain = (IS_ADMIN_FLAG === true) ? HTTP_CATALOG_SERVER : HTTP_SERVER;
            $block['EMAIL_LOGO_FILE'] = $domain . DIR_WS_CATALOG . 'email/' . EMAIL_LOGO_FILENAME;
        }
        if (empty($block['EMAIL_LOGO_ALT_TEXT'])) {
            $block['EMAIL_LOGO_ALT_TEXT'] = EMAIL_LOGO_ALT_TITLE_TEXT;
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

        // Obtain the template file to be used
        $template_filename_base = DIR_FS_EMAIL_TEMPLATES . $langfolder . 'email_template_';
        $template_filename_base_en = DIR_FS_EMAIL_TEMPLATES . 'email_template_';
        $template_filename = DIR_FS_EMAIL_TEMPLATES . $langfolder . 'email_template_' . $current_page_base . '.html';

        $filesToTest = [
            $template_filename_base . str_replace(['_extra', '_admin'], '', $module) . '.html',
            $template_filename_base_en . str_replace(['_extra', '_admin'], '', $module) . '.html',
            DIR_FS_EMAIL_TEMPLATES . $langfolder . 'email_template_' . $current_page_base . '.html',
            DIR_FS_EMAIL_TEMPLATES . 'email_template_' . $current_page_base . '.html',
            (!empty($block['EMAIL_TEMPLATE_FILENAME']) ? $block['EMAIL_TEMPLATE_FILENAME'] . '.html' : null),
            $template_filename_base . 'default.html',
            $template_filename_base_en . 'default.html',
        ];
        $found = false;
        foreach ($filesToTest as $val) {
            if (!empty($val) && file_exists($val)) {
                $template_filename = $val;
                $found = true;
                break;
            }
        }
        if (false === $found) {
            if (isset($messageStack)) {
                $messageStack->add('header', 'ERROR: The email template file for (' . $template_filename_base . ') or (' . $template_filename . ') cannot be found.', 'caution');
            }
            return ''; // couldn't find template file, so return an empty string for html message.
        }

        if (!$fh = fopen($template_filename, 'rb')) {   // note: the 'b' is for compatibility with Windows systems
            if (isset($messageStack)) {
                $messageStack->add('header', 'ERROR: The email template file (' . $template_filename_base . ') or (' . $template_filename . ') cannot be opened', 'caution');
            }
        }

        $file_holder = fread($fh, filesize($template_filename));
        fclose($fh);

        //strip linebreaks and tabs out of the template
//  $file_holder = str_replace(array("\r\n", "\n", "\r", "\t"), '', $file_holder);
        $file_holder = str_replace("\t", ' ', $file_holder);

        if (empty($block['EXTRA_INFO']) || empty(trim($block['EXTRA_INFO']))) {
            $file_holder = preg_replace('/<div class="extra-info">\s?\$EXTRA_INFO\s?<\/div>/', '', $file_holder);
        }

        if (!defined('HTTP_CATALOG_SERVER')) {
            define('HTTP_CATALOG_SERVER', HTTP_SERVER);
        }
        //check for some specifics that need to be included with all messages
        if (empty($block['EMAIL_STORE_NAME'])) {
            $block['EMAIL_STORE_NAME'] = STORE_NAME;
        }
        if (empty($block['EMAIL_STORE_URL'])) {
            $block['EMAIL_STORE_URL'] = '<a href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . '">' . STORE_NAME . '</a>';
        }
        if (empty($block['EMAIL_STORE_OWNER'])) {
            $block['EMAIL_STORE_OWNER'] = STORE_OWNER;
        }
        if (empty($block['EMAIL_FOOTER_COPYRIGHT']) ) {
            $block['EMAIL_FOOTER_COPYRIGHT'] = EMAIL_FOOTER_COPYRIGHT;
        }
        if (empty($block['EMAIL_DISCLAIMER'])) {
            $block['EMAIL_DISCLAIMER'] = sprintf(EMAIL_DISCLAIMER, '<a href="mailto:' . STORE_OWNER_EMAIL_ADDRESS . '">' . STORE_OWNER_EMAIL_ADDRESS . '</a>');
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
        //  if (!isset($block['EMAIL_STYLESHEET']) || $block['EMAIL_STYLESHEET'] == '')      $block['EMAIL_STYLESHEET']       = str_replace(array("\r\n", "\n", "\r"), "",@file_get_contents(DIR_FS_EMAIL_TEMPLATES.'stylesheet.css'));

        if (!isset($block['EXTRA_INFO'])) {
            $block['EXTRA_INFO'] = '';
        }
        if (substr($module, -6) !== '_extra' && $module !== 'contact_us' && $module !== 'ask_a_question') {
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

        //prepare the "unsubscribe" link:
        if (IS_ADMIN_FLAG === true) { // is this admin version, or catalog?
            $block['UNSUBSCRIBE_LINK'] = str_replace("\n", '', TEXT_UNSUBSCRIBE) . ' <a href="' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_catalog_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
        } else {
            $block['UNSUBSCRIBE_LINK'] = str_replace("\n", '', TEXT_UNSUBSCRIBE) . ' <a href="' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '">' . zen_href_link(FILENAME_UNSUBSCRIBE, "addr=" . $block['EMAIL_TO_ADDRESS']) . '</a>';
        }

        //now replace the $BLOCK_NAME items in the template file with the values passed to this function's array
        foreach ($block as $key => $value) {
            $file_holder = str_replace('$' . $key, (string)$value, $file_holder);
        }

        //DEBUG -- to display preview on-screen
        if (EMAIL_SYSTEM_DEBUG === 'preview') {
            echo $file_holder;
        }

        return $file_holder;
    }


    /**
     * Function to build array of additional email content collected and sent on admin-copies of emails:
     *
     */
    function email_collect_extra_info($from, $email_from, $login, $login_email, $login_phone = '', $login_fax = '', $moreinfo = [])
    {
        $email_host_address = '';
        // get host_address from either session or one time for both email types to save server load
        if (empty($_SESSION['customers_host_address'])) {
            if (SESSION_IP_TO_HOST_ADDRESS === 'true' && !empty(trim($_SERVER['REMOTE_ADDR'], '.'))) {
                $email_host_address = gethostbyaddr($_SERVER['REMOTE_ADDR']);
            }
        } else {
            $email_host_address = $_SESSION['customers_host_address'];
        }

        // generate footer details for "also-send-to" emails
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
     * validates an email address
     *
     * Sample Valid Addresses:
     *
     *     first.last@host.com
     *     firstlast@host.to
     *     "first last"@host.com
     *     "first@last"@host.com
     *     first-last@host.com
     *     first's-address@email.host.4somewhere.com
     *     first.last@[123.123.123.123]
     *     lastfirst@mail.international
     *
     *     Invalid Addresses:
     *     first last@host.com
     *     'first@host.com
     *
     * @param string $email address to validate
     * @return boolean
     **/
    function zen_validate_email($email)
    {
        global $zco_notifier;
        $valid_address = true;

        // fail if contains no @ symbol or more than one @ symbol
        if (substr_count($email, '@') != 1) {
            return false;
        }

        // split the email address into user and domain parts
        // this method will most likely break in that case
        [$user, $domain] = explode('@', $email);
        $valid_ip4_form = '[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}';
        $valid_email_pattern = '^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+(XN\-\-[a-z0-9]{2,20}|[a-z]{2,20}))|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$';

        // strip beginning and ending quotes, if and only if both present
        if (strpos($user, '"') === 0 && substr($user, -1) === '"') {
            $user = trim($user, '"');
            $user = str_replace(' ', '', $user); //spaces in quoted addresses OK per RFC (?)
            $email = $user . '@' . $domain; // contine with stripped quotes for remainder
        }

        // fail if contains spaces in domain name
        if (strpos($domain, ' ') !== false) {
            return false;
        }

        // if email domain part is an IP address, check each part for a value under 256
        if (preg_match('/' . $valid_ip4_form . '/', $domain)) {
            $digit = explode('.', $domain);
            for ($i = 0; $i < 4; $i++) {
                if ($digit[$i] > 255) {
                    $valid_address = false;
                    return $valid_address;
                }
                // stop crafty people from using internal IP addresses
                if (($digit[0] == 192) || ($digit[0] == 10)) {
                    $valid_address = false;
                    return $valid_address;
                }
            }
        }

        if (!preg_match('/' . $valid_email_pattern . '/i', $email)) { // validate against valid email pattern
            $valid_address = false;
            return $valid_address;
        }

        $zco_notifier->notify('NOTIFY_EMAIL_VALIDATION_TEST', [$email, $valid_address]);

        return $valid_address;
    }

    /**
     * PROCESS EMBEDDED IMAGES
     * attach and properly embed any embedded images marked as 'embed="yes"'
     *
     * @param string $email_html
     * return string
     */
    function processEmbeddedImages($email_html, &$mail)
    {
        if (defined('EMAIL_ATTACH_EMBEDDED_IMAGES') && EMAIL_ATTACH_EMBEDDED_IMAGES === 'Yes') {
            $imageFiles = [];
            $imagesToProcess = [];
            if (preg_match_all('#<img.*src=\"(.*?)\".*?\/>#', $email_html, $imagesToProcess)) {
                for ($i = 0, $n = count($imagesToProcess[0]); $i < $n; $i++) {
                    $exists = strpos($imagesToProcess[0][$i], 'embed="yes"');
                    if ($exists !== false) {
                        // prevent duplicate attachments - if already processed, remember it
                        if (array_key_exists($imagesToProcess[1][$i], $imageFiles)) {
                            $substitute = $imageFiles[$imagesToProcess[1][$i]];

                            // if not a duplicate, and file can be located on filesystem, add it as an attachment, and replace its SRC attribute with the embedded code
                        } elseif (file_exists(DIR_FS_CATALOG . $imagesToProcess[1][$i])) {
                            $rpos = strrpos($imagesToProcess[1][$i], '.');
                            $ext = substr($imagesToProcess[1][$i], $rpos + 1);
                            $name = basename($imagesToProcess[1][$i], '.' . $ext);
                            switch (strtolower($ext)) {
                                case 'gif':
                                    $mimetype = 'image/gif';
                                    break;
                                case 'jpg':
                                case 'jpeg':
                                    $mimetype = 'image/jpeg';
                                    break;
                                case 'png':
                                default:
                                    $mimetype = 'image/png';
                                    break;
                            }
                            $substitute = $name . $i;
                            $mail->AddEmbeddedImage(DIR_FS_CATALOG . $imagesToProcess[1][$i], $substitute, $name . '.' . $ext, 'base64', $mimetype);
                            $imageFiles[$imagesToProcess[1][$i]] = $substitute;
                        }
                        $email_html = str_replace($imagesToProcess[1][$i], 'cid:' . $substitute, $email_html);
                    }
                }
            }
        }
        return $email_html;
    }

    /**
     * return customer email address
     *
     * @param string $customers_id
     * return string
     */
    function zen_get_email_from_customers_id($customers_id)
    {
        global $db;
        $customers_values = $db->Execute("SELECT customers_email_address
                               FROM " . TABLE_CUSTOMERS . "
                               WHERE customers_id = '" . (int)$customers_id . "'");
        if ($customers_values->EOF) {
            return '';
        }
        return $customers_values->fields['customers_email_address'];
    }

    function zen_db_prepare_input_html_safe($string)
    {
        if (is_string($string)) {
            return trim(stripslashes($string));
        } elseif (is_array($string)) {
            foreach ($string as $key => $value) {
                $string[$key] = zen_db_prepare_input($value);
            }
        }
        return $string;
    }
