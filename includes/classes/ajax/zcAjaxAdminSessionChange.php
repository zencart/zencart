<?php
class zcAjaxAdminSessionChange extends base
{
    protected $supportedNames = [
        'imageView',
    ];

    public function change()
    {
        // -----
        // No action if the 'name' isn't recognized.
        //
        if (!in_array($_POST['name'], $this->supportedNames)) {
            return '';
        }

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
