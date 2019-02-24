<?php
/**
 * @copyright Copyright 2003-2017 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  New in v1.6.0 $
 */

namespace App\Controllers\admin;

use App\Controllers\AbstractAdminController;

class Login extends AbstractAdminController
{

    protected $mainTemplate = 'auth/login';

    public function mainExecute()
    {
        $this->tplVarManager->set('errorMessages', []);
        if ($this->request->readPost('action') === 'doLogin') {
            $this->manageLogin();
        }
        if ($this->request->readPost('action') === 'doExpiredLogin') {
            $this->manageExpiredLogin();
        }
        return;
    }


    protected function manageLogin()
    {

        $adminName = $this->request->readPost('admin_name');
        $adminPass = $this->request->readPost('admin_pass');
        $this->tplVarManager->set('adminName', $adminName);
        if (trim($adminName) === '' || trim($adminPass) === '') {
            sleep(4);
            $this->tplVarManager->push('errorMessages', trans('admin/auth.error-wrong-login'));
            zen_record_admin_activity(trans('log-error-wrong-login'), 'warning');
            return;
        }
        list($error, $expired, $message, $redirect) = zen_validate_user_login($adminName, $adminPass);
        if ($redirect !== '') {
            $this->response['redirect'] = $redirect;
            return;
        }
        if ($expired) {
            if ($message == '') $message = sprintf(ERROR_PASSWORD_EXPIRED . ' ' . ERROR_PASSWORD_RULES, ((int)ADMIN_PASSWORD_MIN_LENGTH < 7 ? 7 : (int)ADMIN_PASSWORD_MIN_LENGTH));
            $this->tplVarManager->push('errorMessages', $message);
            $this->view->setMainTemplate('auth/expiredLogin');
            return;
        }
        if ($error) {
            $this->tplVarManager->push('errorMessages', $message);
        }
        return;
    }

    protected function manageExpiredLogin()
    {
        $this->view->setMainTemplate('auth/expiredLogin');
        $adminName = $this->request->readPost('admin_name');
        $oldPass = $this->request->readPost('oldpwd');
        $newPass = $this->request->readPost('newpwd');
        $newPassConfirm = $this->request->readPost('confpwd');
        $this->tplVarManager->set('adminName', $adminName);
        $errors = zen_validate_pwd_reset_request($adminName, $oldPass, $newPass, $newPassConfirm);
        if ($errors) {
            $this->tplVarManager->set('errorMessages', $errors);
        }
        if (!$errors) {
            list($error, $expired, $message, $redirect) = zen_validate_user_login($adminName, $newPass);
            if ($redirect != '') {
                $this->response['redirect'] = $redirect;
            }
            $this->response['redirect'] = zen_admin_href_link(FILENAME_DEFAULT);
        }
    }

}

define('ADMIN_PASSWORD_EXPIRES_INTERVAL', strtotime('- 90 day'));

