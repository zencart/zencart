<?php
declare(strict_types=1);
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id: DrByte 2024 May 28 New in v2.1.0-alpha1 $
 */
require 'includes/application_top.php';

// load standalone Authenticator class
require_once DIR_FS_ADMIN . DIR_WS_CLASSES . 'MultiFactorAuth.php';
$ga = new MultiFactorAuth();

$fieldAttributes = '';
$message = '';
$error = false;
$setup_required = ($_GET['action'] ?? '') === 'setup' || !empty($_SESSION['mfa']['setup_required']);

$mfa_modes_to_select_from = [
    ['id' => '', 'text' => PLEASE_SELECT],
    ['id' => 'totp', 'text' => TEXT_MFA_METHOD_TOTP],
    ['id' => 'email', 'text' => TEXT_MFA_METHOD_EMAIL],
];

// If a manual token was generated, check its expiry; if the token has expired, reset and redirect to login via logout.
if (isset($_SESSION['mfa']['expires']) && $_SESSION['mfa']['expires'] < time()) {
    unset($_SESSION['mfa'], $_SESSION['admin_id']);
    zen_redirect(zen_href_link(FILENAME_LOGOFF));
}

if (empty($_SESSION['mfa']) || str_starts_with($_POST['action'] ?? '', 'setup') || $setup_required) {
    $user = zen_read_user(zen_get_admin_name($_SESSION['admin_id']));
    $user_mfa_data = json_decode($user['mfa'] ?? '', true, 2);
    $mfa_status_of_store = MFA_ENABLED === 'True';
    $mfa_otp_status = !empty($user_mfa_data['generated_at']) && !empty($user_mfa_data['secret']);
    $mfa_email_status = !empty($user_mfa_data['via_email']);
    $mfa_exempt = !empty($user_mfa_data['exempt']);

    $setup_required = ($mfa_status_of_store && !$mfa_exempt && !$mfa_email_status && !$mfa_otp_status);
}

if (!empty($_POST['action'])) {
    // CSRF
    if (!isset($_SESSION['securityToken'], $_POST['securityToken']) || ($_SESSION['securityToken'] !== $_POST['securityToken'])) {
        $error = true;
        $message = ERROR_SECURITY_ERROR;
        zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_MFA_LOGIN_WITHOUT_CSRF_TOKEN, 'warning');
    } elseif ($_POST['action'] === 'otp' . $_SESSION['securityToken']) {
        // handle session timeout: redirect back to login to start over
        if (empty($_SESSION['mfa'])) {
            unset($_SESSION['mfa']); // for thoroughness
            zen_redirect(zen_href_link(FILENAME_LOGOFF));
        }
        // validate manual-generated token such as one sent via email
        if (isset($_SESSION['mfa']['expires']) && !empty($_SESSION['mfa']['token'])) {
            if (trim($_POST['mfa_code']) === $_SESSION['mfa']['token']) {
                // check for re-used code
                if (zen_check_if_mfa_token_is_reused($_POST['mfa_code'], $_SESSION['mfa']['admin_name'] ?? zen_get_admin_name($_SESSION['admin_id']))) {
                    // re-use of already-used token is a security violation, so log the user out
                    zen_redirect(zen_href_link(FILENAME_LOGOFF));
                }
                // store used token code as expired, to prevent re-use by hijacked devices
                zen_db_perform(TABLE_ADMIN_EXPIRED_TOKENS, ['admin_name' => $_SESSION['mfa']['admin_name'] ?? zen_get_admin_name($_SESSION['admin_id']), 'otp_code' => $_POST['mfa_code']]);

                unset($_SESSION['mfa']);
                $redirect = zen_href_link($_GET['camefrom'] ?? FILENAME_DEFAULT, zen_get_all_get_params(['camefrom', 'action']), 'SSL');
                zen_redirect($redirect);
            }
        } else {
            // validate OTP token
            if ($ga->verifyCode($_SESSION['mfa']['secret'], $_POST['mfa_code'], 2) === true) {
                // check for re-used code
                if (zen_check_if_mfa_token_is_reused($_POST['mfa_code'], $_SESSION['mfa']['admin_name'] ?? zen_get_admin_name($_SESSION['admin_id']))) {
                    // re-use of already-used token is a security violation, so log the user out
                    zen_redirect(zen_href_link(FILENAME_LOGOFF));
                }
                // store secret if this is first validated otp code from qr code
                if (!empty($_SESSION['mfa']['secret_not_yet_persisted'])) {
                    // store validated secret
                    zen_db_perform(TABLE_ADMIN, ['mfa' => json_encode(['secret' => $_SESSION['mfa']['secret'], 'generated_at' => time()])], 'update', "admin_id = " . (int)$_SESSION['mfa']['admin_id']);
                }
                // store used token code as expired, to prevent re-use by hijacked devices
                zen_db_perform(TABLE_ADMIN_EXPIRED_TOKENS, ['admin_name' => $_SESSION['mfa']['admin_name'] ?? zen_get_admin_name($_SESSION['admin_id']), 'otp_code' => $_POST['mfa_code']]);

                unset($_SESSION['mfa']);
                $redirect = zen_href_link($_GET['camefrom'] ?? FILENAME_DEFAULT, zen_get_all_get_params(['camefrom', 'action']), 'SSL');
                zen_redirect($redirect);
            }
        }

        // bad code was entered; let them try again
        sleep(2);
        $error = true;
        $message = ERROR_WRONG_CODE;
        zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_MFA_LOGIN_WITH_INVALID_CODE, 'warning');

    } elseif ($_POST['action'] === 'setup' . $_SESSION['securityToken']) {
        if ($_POST['selected'] === 'email') {
            zen_db_perform(TABLE_ADMIN, ['mfa' => json_encode(['via_email' => true])], 'update', "admin_id = " . (int)$_SESSION['admin_id']);
            zen_mfa_by_email(['admin_id' => $user['admin_id'], 'email' => $user['admin_email'], 'admin_name' => $user['admin_name'], 'mfa' => json_encode(['via_email' => true])]);
            $redirect = zen_href_link($_GET['camefrom'] ?? FILENAME_DEFAULT, zen_get_all_get_params(['camefrom', 'action']), 'SSL');
            zen_redirect($redirect);
        }
        // else set up OTP
        $setup_required = false;
        zen_mfa_by_totp(['admin_id' => $user['admin_id'], 'email' => $user['admin_email'], 'admin_name' => $user['admin_name'], 'mfa' => $user['mfa']]);
    }
}

// set some field validation attributes
$length = (int)($_SESSION['mfa']['length'] ?? 0);
if ($length > 0) {
    $fieldAttributes = ' size="' . ($length + 1) . '" maxlength="' . $length . '"';
}
$fieldAttributes .= match ($_SESSION['mfa']['type'] ?? 'digits') {
    'digits' => 'inputmode="numeric" pattern="[0-9]*"',
    'alphanum' => 'pattern="[a-zA-Z0-9]*"',
    'alpha' => 'pattern="[a-zA-Z]*"',
    default => '',
};
?>
<!DOCTYPE html>
<html <?= HTML_PARAMS ?>>
<head>
    <?php
    require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <meta name="robots" content="noindex, nofollow">
</head>
<body id="mfa">
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-xs-offset-2 col-sm-offset-3 col-md-offset-3 col-lg-offset-4 col-xs-8 col-sm-6 col-md-6 col-lg-4 text-center">
            <div class="row">
                <div class="col-sm-12 mfa-main-div mfa-box-shadow">
                    <?= zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT, 'class="mfa-img"') . PHP_EOL ?>
                    <?= zen_draw_form('mfaForm', FILENAME_MFA, zen_get_all_get_params(), 'post', 'id="mfaForm" class="form-horizontal"', 'true') . PHP_EOL ?>

                    <?php if ($setup_required) { ?>
                    <?= zen_draw_hidden_field('action', 'setup' . $_SESSION['securityToken'], 'id="otpsetup"') . PHP_EOL ?>
                    <h2><?= TEXT_MFA_SELECT ?></h2>
                    <div class="form-group form-group">
                        <?= zen_draw_pull_down_menu('selected', $mfa_modes_to_select_from, 'totp', 'class="form-control input" autofocus id="mfaselect-' . $_SESSION['securityToken'] . '" required') . PHP_EOL ?>
                    </div>

                    <?php } else { ?>

                    <?= zen_draw_hidden_field('action', 'otp' . $_SESSION['securityToken'], 'id="otpaction"') . PHP_EOL ?>

                    <h2><?= HEADING_TITLE ?></h2>


                    <?php if (empty($_SESSION['mfa']['qrcode'])) { ?>
                    <div id="mfa-intro" class="col-xs-12 mt-3">
                        <p><?= ''//TEXT_MFA_INTRO ?></p>
                    </div>
                    <?php } ?>

                    <?php
                    if (!empty($_SESSION['mfa']['qrcode'])) { ?>
                        <div id="mfa-qrcode" class="col-xs-12 m-4">
                            <?= TEXT_MFA_SCAN_QR_CODE ?><br><br>
                            <div id="mfa_qr_img"><?php
                                $qrCode = $_SESSION['mfa']['qrcode'];
                                if (str_starts_with($qrCode, '<')) {
                                    echo $qrCode;
                                } else {
                                    echo sprintf('<img class="text-center" src="%s" alt="QR Code"/>', $qrCode);
                                }
                                ?></div>
                        </div>
                    <?php
                    } ?>
                    <div class="row">
                        <div class="col-sm-8 col-sm-offset-2">
                            <div class="form-group">
                                <?php
                                if (empty($_SESSION['mfa']['qrcode'])) {
                                    if (isset($_SESSION['mfa']['expires']) && !empty($_SESSION['mfa']['token'])) {
                                        echo TEXT_MFA_ENTER_OTP_EMAIL;
                                    } else {
                                        echo TEXT_MFA_ENTER_OTP_CODE;
                                    }
                                }
                                ?>
                                <div class="input-group mt-4">
                                    <span class="input-group-addon"><i class="fa-solid fa-lg fa-lock"></i></span>
                                    <?= zen_draw_input_field('mfa_code', '', 'class="form-control input-md" autocapitalize="none" spellcheck="false" autocomplete="one-time-code" autofocus placeholder="' . TEXT_MFA_INPUT . '"' . $fieldAttributes . ' id="mfa-' . $_SESSION['securityToken'] . '" required', false, 'text') . PHP_EOL ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>


                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?= TEXT_SUBMIT ?></button>
                        <a class="button btn btn-default btn-link" href="<?= zen_href_link(FILENAME_LOGOFF) ?>"><?= TEXT_CANCEL ?></a>
                    </div>
                    <?php
                    echo '</form>' . PHP_EOL; ?>
                    <br class="clearBoth">
                    <?php
                    if ($message) { ?>
                        <p class="mfa-alert-warning alert alert-warning"><?= $message ?></p>
                    <?php
                    } ?>
                    <div id="mfa-bottom" class="m-3">
                        <p><?= TEXT_MFA_BOTTOM ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
require 'includes/application_bottom.php';
