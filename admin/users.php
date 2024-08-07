<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2024 May 24 Modified in v2.1.0-alpha1 $
 */
require('includes/application_top.php');

// Check if session has timed out
if (!isset($_SESSION['admin_id'])) {
    zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
}

// note the current user so they cannot change their own profile nor delete themselves
$currentUser = $_SESSION['admin_id'];

// determine whether an action has been requested
if (isset($_POST['action']) && in_array($_POST['action'], ['insert', 'update', 'reset'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action']) && in_array($_GET['action'], ['add', 'edit', 'password', 'delete', 'delete_confirm', 'deletemfa', 'deletemfa_confirm', 'exemptmfa', 'exemptmfa_confirm', 'unexemptmfa', 'unexemptmfa_confirm'])) {
    $action = $_GET['action'];
} else {
    $action = '';
}

// check for a valid user id (not for add)
if (($action == 'update' || $action == 'reset') && isset($_POST['user'])) {
    $user = $_POST['user'];
} elseif (($action == 'edit' || $action == 'password') && isset($_GET['user'])) {
    $user = $_GET['user'];
} elseif (($action == 'delete' || $action == 'delete_confirm') && isset($_POST['user'])) {
    $user = $_POST['user'];
} elseif (($action === 'deletemfa' || $action === 'deletemfa_confirm') && isset($_POST['user'])) {
    $user = $_POST['user'];
} elseif (($action === 'exemptmfa' || $action === 'exemptmfa_confirm') && isset($_POST['user'])) {
    $user = $_POST['user'];
} elseif (($action === 'unexemptmfa' || $action === 'unexemptmfa_confirm') && isset($_POST['user'])) {
    $user = $_POST['user'];
} elseif (in_array($action, ['edit', 'password', 'delete', 'delete_confirm', 'update', 'reset', 'deletemfa', 'deletemfa_confirm', 'exemptmfa', 'exemptmfa_confirm', 'unexemptmfa', 'unexemptmfa_confirm'])) {
    $messageStack->add_session(ERROR_NO_USER_DEFINED, 'error');
    zen_redirect(zen_href_link(FILENAME_USERS));
}

//set form action based on button selection
switch ($action) {
    case 'add': // display unpopulated form for adding a new user
        $formAction = 'insert';
        $profilesList = array_merge([['id' => null, 'text' => TEXT_CHOOSE_PROFILE]], zen_get_profiles());
        break;
    case 'edit': // display populated form for editing existing user Name/Email/Profile
        $formAction = 'update';
        $profilesList = array_merge([['id' => null, 'text' => TEXT_CHOOSE_PROFILE]], zen_get_profiles());
        break;
    case 'password': // display form input fields for resetting existing user's Password
        $formAction = 'reset';
        break;
    case 'delete_confirm': // remove existing user from database
        if (isset($_POST['user'])) {
            zen_delete_user($_POST['user']);
        }
        break;
    case 'deletemfa_confirm': // remove mfa from user account
        if (isset($_POST['user'])) {
            zen_db_perform(TABLE_ADMIN, ['mfa' => 'NULL'], 'update', 'admin_id = ' . (int)$_POST['user']);
            $uname = preg_replace('/[^\w._-]/', '*', zen_get_admin_name($_POST['user'])) . ' [id: ' . (int)$_POST['user'] . ']';
            $admname = '{' . preg_replace('/[^\w._-]/', '*', zen_get_admin_name()) . ' [id: ' . (int)$_SESSION['admin_id'] . ']}';
            zen_record_admin_activity(sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_DELETED, $uname, $admname), 'warning');
            $email_text = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_DELETED, $uname, $admname);
            $block = ['EMAIL_MESSAGE_HTML' => $email_text];
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_MFA_DELETED, $email_text, STORE_NAME, EMAIL_FROM, $block, 'admin_settings_changed');
        }
        break;
    case 'exemptmfa_confirm': // mark user account to be excluded from mfa
        if (isset($_POST['user'])) {
            zen_db_perform(TABLE_ADMIN, ['mfa' => json_encode(['exempt' => true])], 'update', 'admin_id = ' . (int)$_POST['user']);
            $uname = preg_replace('/[^\w._-]/', '*', zen_get_admin_name($_POST['user'])) . ' [id: ' . (int)$_POST['user'] . ']';
            $admname = '{' . preg_replace('/[^\w._-]/', '*', zen_get_admin_name()) . ' [id: ' . (int)$_SESSION['admin_id'] . ']}';
            zen_record_admin_activity(sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_EXEMPTED, $uname, $admname), 'warning');
            $email_text = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_EXEMPTED, $uname, $admname);
            $block = ['EMAIL_MESSAGE_HTML' => $email_text];
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_MFA_EXEMPTED, $email_text, STORE_NAME, EMAIL_FROM, $block, 'admin_settings_changed');
        }
        break;
    case 'unexemptmfa_confirm': // undo mfa exemption
        if (isset($_POST['user'])) {
            zen_db_perform(TABLE_ADMIN, ['mfa' => json_encode(['exempt' => false])], 'update', 'admin_id = ' . (int)$_POST['user']);
            $uname = preg_replace('/[^\w._-]/', '*', zen_get_admin_name($_POST['user'])) . ' [id: ' . (int)$_POST['user'] . ']';
            $admname = '{' . preg_replace('/[^\w._-]/', '*', zen_get_admin_name()) . ' [id: ' . (int)$_SESSION['admin_id'] . ']}';
            zen_record_admin_activity(sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_UNEXEMPTED, $uname, $admname), 'warning');
            $email_text = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MFA_UNEXEMPTED, $uname, $admname);
            $block = ['EMAIL_MESSAGE_HTML' => $email_text];
            zen_mail(STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_MFA_UNEXEMPTED, $email_text, STORE_NAME, EMAIL_FROM, $block, 'admin_settings_changed');
        }
        break;
    case 'insert': // insert new user into database. Post data is prep'd for db in the first function call
        $errors = zen_insert_user($_POST['name'], $_POST['email'], $_POST['password'], $_POST['confirm'], $_POST['profile']);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messageStack->add($error, 'error');
            }
            $action = 'add';
            $formAction = 'insert';
            $profilesList = array_merge([['id' => null, 'text' => TEXT_CHOOSE_PROFILE]], zen_get_profiles());
        } else {
            $action = '';
            $messageStack->add(sprintf(SUCCESS_NEW_USER_ADDED, zen_output_string_protected($_POST['name'])), 'success');
        }
        break;
    case 'update': // update existing user's details in database. Post data is prep'd for db in the first function call
        $errors = zen_update_user($_POST['name'], $_POST['email'], $_POST['id'], $_POST['profile']);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $messageStack->add($error, 'error');
            }
            $action = 'edit';
            $formAction = 'update';
            $profilesList = array_merge([['id' => null, 'text' => TEXT_CHOOSE_PROFILE]], zen_get_profiles());
        } else {
            $action = '';
            $messageStack->add(SUCCESS_USER_DETAILS_UPDATED, 'success');
        }
        break;
    case 'reset': // reset existing user's password in database. Post data is prep'd for db in the first function call
        $errors = zen_reset_password($_POST['user'], $_POST['password'], $_POST['confirm']);
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

// list of users
$userList = zen_get_users();
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
    <?php if ($action !== '' && !in_array($action, ['delete', 'deletemfa', 'exemptmfa', 'unexemptmfa'])) { // Hide this form when delete selected
        echo zen_draw_form('users', FILENAME_USERS);
        if (isset($formAction)) {
            echo zen_draw_hidden_field('action', $formAction);
        }
    }
    if ($action == 'edit' || $action == 'password') {
        echo zen_draw_hidden_field('user', $user);
    } ?>
    <table class="table table-striped">
        <thead>
        <tr class="headingRow">
            <th class="id"><?php echo TEXT_ID ?></th>
            <th class="name"><?php echo TEXT_ADMIN_NAME ?></th>
            <th class="email"><?php echo TEXT_EMAIL ?></th>
            <th class="profile"><?php echo TEXT_ADMIN_PROFILE ?></th>
            <th class="changed"><?php echo TEXT_PASS_LAST_CHANGED ?></th>
            <?php if (zen_is_superuser()) { ?>
            <th class="mfa_status"><?php echo TEXT_MFA_STATUS ?></th>
            <?php } ?>
            <?php if ($action == 'add' || $action == 'password') { ?>
                <th class="password"><?php echo TEXT_PASSWORD ?></th>
                <th class="password"><?php echo TEXT_CONFIRM_PASSWORD ?></th>
            <?php } ?>
            <th class="actions">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($action == 'add') { ?>
            <tr>
                <td class="id">&nbsp;</td>
                <td class="name"><?php echo zen_draw_input_field('name', isset($_POST['name']) ? $_POST['name'] : '', 'class="form-control" autofocus autocomplete="off"', true, 'text', true) ?></td>
                <td class="email"><?php echo zen_draw_input_field('email', isset($_POST['email']) ? $_POST['email'] : '', 'class="form-control" autocomplete="off"', true, 'email', true) ?></td>
                <td class="profile"><?php echo zen_draw_pull_down_menu('profile', $profilesList, isset($_POST['profile']) ? $_POST['profile'] : '', 'class="form-control"', true) ?></td>
                <td class="changed"></td>
                <?php if (zen_is_superuser()) { ?>
                <td class="mfa_status"></td>
                <?php } ?>
                <td class="password"><?php echo zen_draw_input_field('password', isset($_POST['password']) ? $_POST['password'] : '', 'class="form-control" autocomplete="off"', true, 'password'); ?></td>
                <td class="confirm"><?php echo zen_draw_input_field('confirm', isset($_POST['confirm']) ? $_POST['confirm'] : '', 'class="form-control" autocomplete="off"', true, 'password'); ?></td>
                <td class="actions">
                    <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                    <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a></td>
            </tr>
        <?php } ?>
        <?php if (count($userList) > 0) { ?>
            <?php foreach ($userList as $userDetails) { ?>
                <tr>
                <?php if (($action == 'edit' || $action == 'password') && $user == $userDetails['id']) { ?>
                    <td class="id"><?php echo $userDetails['id'] ?><?php echo zen_draw_hidden_field('id', $userDetails['id']) ?></td>
                <?php } else { ?>
                    <td class="id"><?php echo $userDetails['id'] ?></td>
                <?php } ?>
                <?php if ($action == 'edit' && $user == $userDetails['id']) { ?>
                    <td class="name"><?php echo zen_draw_input_field('name', $userDetails['name'], 'class="form-control"') ?></td>
                    <td class="email"><?php echo zen_draw_input_field('email', $userDetails['email'], 'class="form-control"', false, 'email') ?></td>
                <?php } else { ?>
                    <td class="name"><?php echo $userDetails['name'] ?></td>
                    <td class="email"><?php echo $userDetails['email'] ?></td>
                <?php } ?>
                <?php if ($action == 'edit' && $user == $userDetails['id'] && $user != $currentUser) { // do not allow current user to change profile ?>
                    <td class="profile"><?php echo zen_draw_pull_down_menu('profile', $profilesList, $userDetails['profile'], 'class="form-control"') ?></td>
                <?php } else { ?>
                    <td class="profile"><?php echo $userDetails['profileName'] ?></td>
                <?php } ?>
                <td class="changed"><?php echo zen_date_short($userDetails['pwd_last_change_date']); ?></td>
                <?php
                if (zen_is_superuser()) {
                    $userFresh = zen_read_user($userDetails['name']);
                    $user_mfa_data = json_decode($userFresh['mfa'] ?? '', true, 2);
                    $mfa_status_of_store = MFA_ENABLED === 'True';
                    $mfa_status = !empty($user_mfa_data['generated_at']) && !empty($user_mfa_data['secret']);
                    $mfa_exempt = !empty($user_mfa_data['exempt']);
                    $mfa_date = $mfa_status ? (new DateTime)->setTimestamp($user_mfa_data['generated_at'])->setTimezone((new DateTime)->getTimezone())->format('Y-m-d H:i:s') : '';
                    $mfa_status_msg = TEXT_MFA_DISABLED_FOR_SITE;
                    if ($mfa_status_of_store) {
                        $mfa_status_msg = TEXT_MFA_NOT_SET;
                    }
                    if (!empty($user_mfa_data['generated_at'])) {
                        $mfa_status_msg = sprintf(TEXT_MFA_ENABLED_DATE, zen_date_short($mfa_date));
                    } elseif (!empty($user_mfa_data['via_email'])) {
                        $mfa_status_msg = TEXT_MFA_BY_EMAIL;
                    } elseif ($mfa_exempt) {
                        $mfa_status_msg = TEXT_MFA_EXEMPT;
                    }
                    ?>
                <td class="mfa_status"><?= $mfa_status_msg ?>
                    <?php if ($mfa_status_of_store !== true) {
                        // not enabled, so no buttons to output
                    } elseif ($mfa_status === true || !empty($user_mfa_data['via_email'])) {
                        $btn_class = '';
                        if ($action === 'deletemfa' && $userDetails['id'] === $user) {
                           $btn_class = 'btn btn-sm btn-danger';
                        } elseif ($action !== 'deletemfa') {
                            $btn_class = 'btn btn-sm btn-warning';
                        }
                        ?>
                        <?php echo zen_draw_form('delete_mfa', FILENAME_USERS, 'action=' . ($action === 'deletemfa' ? 'deletemfa_confirm' : 'deletemfa')); ?>
                        <?php echo zen_draw_hidden_field('user', $userDetails['id']); ?>
                        <?php echo ($action === 'deletemfa' && $userDetails['id'] === $user ? '<br>' . TEXT_CONFIRM_RESET : '') . ($btn_class === '' ? '' : '<button class="' . $btn_class . '">' . IMAGE_RESET . '</button>') ?>
                        <?php if ($action === 'deletemfa' && $userDetails['id'] === $user) { ?>
                            <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-sm btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        <?php } ?>
                        <?php echo '</form>'; ?>
                    <?php
                    } elseif ($mfa_exempt !== true) {
                        $btn_class = '';
                        if ($action === 'exemptmfa' && $userDetails['id'] === $user) {
                           $btn_class = 'btn btn-sm btn-danger';
                        } elseif ($action !== 'exemptmfa') {
                            $btn_class = 'btn btn-sm btn-default';
                        }
                        ?>
                        <?php echo zen_draw_form('exempt_mfa', FILENAME_USERS, 'action=' . ($action === 'exemptmfa' ? 'exemptmfa_confirm' : 'exemptmfa')); ?>
                        <?php echo zen_draw_hidden_field('user', $userDetails['id']); ?>
                        <?php echo ($action === 'exemptmfa' && $userDetails['id'] === $user ? '<br>' . TEXT_CONFIRM_EXEMPT : '') . ($btn_class === '' ? '' : '<button class="' . $btn_class . '">' . TEXT_BUTTON_EXEMPT . '</button>') ?>
                        <?php if ($action === 'exemptmfa' && $userDetails['id'] === $user) { ?>
                            <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-sm btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        <?php } ?>
                        <?php echo '</form>'; ?>
                    <?php } elseif ($mfa_exempt === true) {
                        $btn_class = '';
                        if ($action === 'unexemptmfa' && $userDetails['id'] === $user) {
                           $btn_class = 'btn btn-sm btn-danger';
                        } elseif ($action !== 'ununexemptmfa') {
                            $btn_class = 'btn btn-sm btn-default';
                        }
                        ?>
                        <?php echo zen_draw_form('unexempt_mfa', FILENAME_USERS, 'action=' . ($action === 'unexemptmfa' ? 'unexemptmfa_confirm' : 'unexemptmfa')); ?>
                        <?php echo zen_draw_hidden_field('user', $userDetails['id']); ?>
                        <?php echo ($action === 'unexemptmfa' && $userDetails['id'] === $user ? '<br>' . TEXT_CONFIRM_UNEXEMPT : '') . ($btn_class === '' ? '' : '<button class="' . $btn_class . '">' . TEXT_BUTTON_UNEXEMPT . '</button>') ?>
                        <?php if ($action === 'unexemptmfa' && $userDetails['id'] === $user) { ?>
                            <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-sm btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        <?php } ?>
                        <?php echo '</form>'; ?>
                    <?php } ?>
                </td>
                <?php } ?>
                <?php if ($action == 'password' && $user == $userDetails['id']) { ?>
                    <td class="password"><?php echo zen_draw_input_field('password', '', 'class="form-control"', true, 'password', true) ?></td>
                    <td class="confirm"><?php echo zen_draw_input_field('confirm', '', 'class="form-control"', true, 'password', true) ?></td>
                <?php } elseif ($action == 'add' || $action == 'password') { ?>
                    <td class="password">&nbsp;</td>
                    <td class="confirm">&nbsp;</td>
                <?php } ?>
                <?php if ($action == 'add' || $action == 'edit' || $action == 'password') { ?>
                    <?php if ($action != 'add' && $user == $userDetails['id']) { ?>
                        <td class="actions">
                            <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                            <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                        </td>
                    <?php } else { ?>
                        <td class="actions">&nbsp;</td>
                    <?php } ?>
                <?php } elseif ($action != 'add') { ?>
                    <td class="actions">
                        <?php if ($action != 'delete') { ?>
                            <a href="<?php echo zen_href_link(FILENAME_USERS, 'action=edit&user=' . $userDetails['id']) ?>" class="btn btn-primary" role="button"><?php echo IMAGE_EDIT; ?></a>
                            <a href="<?php echo zen_href_link(FILENAME_USERS, 'action=password&user=' . $userDetails['id']) ?>" class="btn btn-primary"><?php echo IMAGE_RESET_PWD; ?></a>
                        <?php } ?>
                        <?php
                        if ($userDetails['id'] != $currentUser) {
                            $btn_class = '';
                            if ($action == 'delete' && $userDetails['id'] == $user) {
                                $btn_class = 'btn btn-danger';
                            } elseif ($action != 'delete') {
                                $btn_class = 'btn btn-warning';
                            }
                            ?>
                            <?php echo zen_draw_form('delete_user', FILENAME_USERS, 'action=' . ($action == 'delete' ? 'delete_confirm' : 'delete')); ?>
                            <?php echo zen_draw_hidden_field('user', $userDetails['id']); ?>
                            <?php echo ($action == 'delete' && $userDetails['id'] == $user ? TEXT_CONFIRM_DELETE : '') . ($btn_class == '' ? '' : '<button class="' . $btn_class . '">' . IMAGE_DELETE . '</button>') ?>
                            <?php if ($action == 'delete' && $userDetails['id'] == $user) { ?>
                                <a href="<?php echo zen_href_link(FILENAME_USERS) ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
                            <?php } ?>
                            <?php echo '</form>'; ?>
                        <?php } ?>
                    </td>
                    </tr>
                    <?php
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="5"><?php echo TEXT_NO_USERS_FOUND ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <?php if ($action == '' || $action == 'delete_confirm') { ?>
        <div><a href="<?php echo zen_href_link(FILENAME_USERS, 'action=add'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_ADD_USER; ?></a></div>
    <?php }
    if ($action != '' && $action != 'delete') {
        echo '</form>';
    } ?>
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
