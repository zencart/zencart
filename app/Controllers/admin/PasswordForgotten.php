<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */

namespace App\Controllers\admin;

use App\Controllers\AbstractAdminController;

class PasswordForgotten extends AbstractAdminController
{

    protected $mainTemplate = 'auth/passwordForgotten';

    public function mainExecute()
    {
        $this->tplVarManager->set('errorMessages', []);

        if ($this->request->readPost('admin_email', false) === false) {
            return;
        }

        if ($this->request->getSession()->get('reset_attempts', 0) > 9) {
            header('HTTP/1.1 406 Not Acceptable');
            return;
        }

        $adminEmail = $this->request->readPost('admin_email', '');

        if ($adminEmail === '') {
            $this->tplVarManager->push('errorMessages', trans('admin/auth.error-invalid-reset-email'));
        }

        $this->request->getSession()->increment('reset_attempts');

        $adminModel = $this->modelFactory->make('Admin');

        $adminModel = $adminModel->where('admin_email', '=', $adminEmail)->first();

        $this->tplVarManager->set('reset', true);


        if ($adminEmail !== $adminModel['admin_email']) {
            return;
        }

        $newPassword = zen_create_PADSS_password(
            (int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)
            ADMIN_PASSWORD_MIN_LENGTH);

        $resetToken = (time() + ADMIN_PWD_TOKEN_DURATION) . '}' . zen_encrypt_password($newPassword);

        $adminModel->update(['reset_token' => $resetToken,]);

        $html_msg['EMAIL_CUSTOMERS_NAME'] = $adminModel['admin_name'];
        $html_msg['EMAIL_MESSAGE_HTML'] = sprintf(TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $newPassword);
        zen_mail(
            $adminModel['admin_name'], $adminModel['admin_email'], TEXT_EMAIL_SUBJECT_PWD_RESET, sprintf
        (
            TEXT_EMAIL_MESSAGE_PWD_RESET, $_SERVER['REMOTE_ADDR'], $newPassword), STORE_NAME, EMAIL_FROM, $html_msg,
            'password_forgotten_admin');

        $this->view->setMainTemplate('auth/resetTokenSent');

    }

}
