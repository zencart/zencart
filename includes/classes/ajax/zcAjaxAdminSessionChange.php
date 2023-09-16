<?php

class zcAjaxAdminSessionChange extends base
{
    public function change()
    {
        if (!isset($_SESSION[$_POST['name']])) {
            $_SESSION[$_POST['name']] = true;

        } elseif ($_SESSION[$_POST['name']] === true) {
            $_SESSION[$_POST['name']] = false;
            return $_POST['name'] . ' set to false!';
        }

        $_SESSION[$_POST['name']] = true;
        return $_POST['name'] . ' set to true!';
    }

}
