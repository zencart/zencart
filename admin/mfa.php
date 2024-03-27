<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License v2.0
 * @version $Id:  New in v2.0 $
 */
require 'includes/application_top.php';

$fieldAttributes = '';
$message = '';
$error = false;

// If the token has expired, we must reset and redirect to login via logout.
if (isset($_SESSION['mfa']['expires']) && $_SESSION['mfa']['expires'] < time()) {
    unset($_SESSION['mfa'], $_SESSION['admin_id']);
    zen_redirect(zen_href_link(FILENAME_LOGOFF));
}

$length = (int)($_SESSION['mfa']['length'] ?? 0);
if ($length > 0) {
    $fieldAttributes = ' size="' . ($length + 1) . '" maxlength="' . $length . '"';
}
$fieldAttributes .= match ($_SESSION['mfa']['type']) {
    'digits' => 'inputmode="numeric" pattern="[0-9]*"',
    'alphanum' => 'pattern="[a-zA-Z0-9]*"',
    'alpha' => 'pattern="[a-zA-Z]*"',
    default => '',
};

if (isset($_POST['action']) && $_POST['action'] !== '') {
    if (!isset($_SESSION['securityToken'], $_POST['securityToken']) || ($_SESSION['securityToken'] !== $_POST['securityToken'])) {
        $error = true;
        $message = ERROR_SECURITY_ERROR;
        zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_MFA_LOGIN_WITHOUT_CSRF_TOKEN, 'warning');
    } elseif ($_POST['action'] === 'otp' . $_SESSION['securityToken']) {

        if ($_SESSION['mfa']['expires'] >= time()) {
            if (trim($_POST['mfa_code']) === $_SESSION['mfa']['token']) {
                unset($_SESSION['mfa']);
                $camefrom = $_GET['camefrom'] ?? FILENAME_DEFAULT;
                $redirect = zen_href_link($camefrom, zen_get_all_get_params(['camefrom']), 'SSL');
                zen_redirect($redirect);
            }
        }

        // bad code was entered
        sleep(2);
        $error = true;
        $message = ERROR_WRONG_CODE;
        zen_record_admin_activity(TEXT_ERROR_ATTEMPTED_ADMIN_MFA_LOGIN_WITH_INVALID_CODE, 'warning');
    }
}
?>
<!DOCTYPE html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <meta name="robots" content="noindex, nofollow">
</head>
<body id="mfa">
<div class="container-fluid">
    <div class="row">
        <div class="col-xs-offset-2 col-sm-offset-3 col-md-offset-4 col-lg-offset-5 col-xs-8 col-sm-6 col-md-4 col-lg-3 text-center">
            <div class="mfa-main-div mfa-box-shadow">
                <?php echo zen_image(DIR_WS_IMAGES . HEADER_LOGO_IMAGE, HEADER_ALT_TEXT, HEADER_LOGO_WIDTH, HEADER_LOGO_HEIGHT, 'class="mfa-img"') . PHP_EOL; ?>
                <?php echo zen_draw_form('mfaForm', FILENAME_MFA, zen_get_all_get_params(), 'post', 'id="mfaForm" class="form-horizontal"', 'true') . PHP_EOL; ?>
                <?php echo zen_draw_hidden_field('action', 'otp' . $_SESSION['securityToken'], 'id="otpaction"') . PHP_EOL; ?>

                <h2><?php echo HEADING_TITLE; ?></h2>
                <div id="mfa-intro" class="m-5">
                    <p><?php echo TEXT_MFA_INTRO; ?></p>
                </div>
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa-solid fa-lg fa-lock"></i></span>
                        <?php echo zen_draw_input_field('mfa_code', '', 'class="form-control input-lg" autocapitalize="none" spellcheck="false" autocomplete="one-time-code" autofocus placeholder="' . TEXT_MFA_INPUT . '"' . $fieldAttributes. ' id="mfa-' . $_SESSION['securityToken'] . '" required', false, 'text') . PHP_EOL; ?>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php echo TEXT_SUBMIT; ?></button>
                    <a class="button" class="btn btn-default btn-link" href="<?php echo zen_href_link(FILENAME_LOGOFF); ?>"><?php echo TEXT_CANCEL; ?></a>
                </div>
                <?php
                echo '</form>' . PHP_EOL; ?>
                <br class="clearBoth">
                <?php if ($message) { ?>
                    <p class="mfa-alert-warning alert alert-warning"><?php echo $message; ?></p>
                <?php } ?>
                <div id="mfa-bottom" class="m-3">
                    <p><?php echo TEXT_MFA_BOTTOM; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

<?php
require 'includes/application_bottom.php';
