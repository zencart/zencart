<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce<br />
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Erik Kerkhoven 2020 Apr 14 Modified in v1.5.7 $
 */
require('includes/application_top.php');

// Check if session has timed out
if (!isset($_SESSION['admin_id'])) {
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

$user = $_SESSION['admin_id'];

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], ['update', 'reset'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], ['edit', 'password'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}

// validate form input as not expired and not spoofed
if ($action != '' && isset($_POST['action']) && $_POST['action'] != '' && $_POST['securityToken'] != $_SESSION['securityToken']) {
    $messageStack->add_session(ERROR_TOKEN_EXPIRED_PLEASE_RESUBMIT, 'error');
    zen_redirect(zen_href_link(FILENAME_ADMIN_ACCOUNT));
}

//set form action based on button selection
switch ($action) {
    case 'edit': // display populated form for editing current user
        $formAction = 'update';
        break;
    case 'password': // display form input fields for resetting existing user's Password
        $formAction = 'reset';
        break;
    case 'update': // update current user's email in database. Post data is prep'd for db in the first function call
        $errors = zen_update_user(false, $_POST['email'], $_SESSION['admin_id'], null);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messageStack->add($error, 'error');
            }
            $action = 'edit';
            $formAction = 'update';
        } else {
            $action = '';
            $messageStack->add(SUCCESS_USER_DETAILS_UPDATED, 'success');
        }
        break;
    case 'reset': // reset current user's password in database. Post data is prep'd for db in the first function call
        $errors = zen_reset_password($_SESSION['admin_id'], $_POST['password'], $_POST['confirm']);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messageStack->add($error, 'error');
            }
            $action = 'password';
            $formAction = 'reset';
        } else {
            $action = '';
            $messageStack->add(SUCCESS_PASSWORD_UPDATED, 'success');
        }
        break;
    default: // no action, simply drop through and display existing users
}

// get this user's details
$userList = zen_get_users($_SESSION['admin_id']);
$userDetails = $userList[0];
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    <link rel="stylesheet" href="includes/css/admin_access.css">
</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div class="container-fluid" id="pageWrapper">

    <h1><?php echo HEADING_TITLE ?></h1>
    <?php
        echo zen_draw_form('users', FILENAME_ADMIN_ACCOUNT);
        if (isset($formAction)) {
            echo zen_draw_hidden_field('action', $formAction);
        }
    ?>
    <table class="table">
        <thead>
        <tr class="headingRow">
            <th class="name"><?php echo TEXT_ADMIN_NAME ?></th>
            <th class="email"><?php echo TEXT_EMAIL ?></th>
            <?php if ($action == 'password') { ?>
                <th class="password"><?php echo TEXT_PASSWORD ?></th>
                <th class="password"><?php echo TEXT_CONFIRM_PASSWORD ?></th>
            <?php } ?>
            <th class="actions">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
            <tr>
            <td class="name"><?php echo $userDetails['name'] ?><?php echo zen_draw_hidden_field('admin_name', $userDetails['name']); ?></td>
                <?php if ($action == 'edit' && $user == $userDetails['id']) { ?>
                    <td class="email"><?php echo zen_draw_input_field('email', $userDetails['email'], 'class="form-control"', false, 'email') ?></td>
                <?php } else { ?>
                    <td class="email"><?php echo $userDetails['email'] ?></td>
                <?php } ?>
                <?php if ($action == 'password' && $user == $userDetails['id']) { ?>
                    <td class="password"><?php echo zen_draw_input_field('password', '', 'class="form-control" required', false, 'password', true) ?></td>
                    <td class="confirm"><?php echo zen_draw_input_field('confirm', '', 'class="form-control" required', false, 'password', true) ?></td>
                <?php } elseif ($action == 'add' || $action == 'password') { ?>
                    <td class="password">&nbsp;</td>
                    <td class="confirm">&nbsp;</td>
                <?php } ?>
                <?php if ($action == 'edit' || $action == 'password') { ?>
                <?php if ($user == $userDetails['id']) { ?>
                        <td class="actions">
                            <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                            <a href="<?php echo zen_href_link(FILENAME_ADMIN_ACCOUNT) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        </td>
                    <?php } else { ?>
                        <td class="actions">&nbsp;</td>
                    <?php } ?>
                <?php } else { ?>
                    <td class="actions">
                        <a href="<?php echo zen_href_link(FILENAME_ADMIN_ACCOUNT, 'action=edit'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a> 
                        <a href="<?php echo zen_href_link(FILENAME_ADMIN_ACCOUNT, 'action=password') ?>" class="btn btn-primary"><?php echo IMAGE_RESET_PWD; ?></a>
                    </td>
                </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php echo '</form>'; ?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
