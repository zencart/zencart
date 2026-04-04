<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v2.0.0
 */

class zcAjaxAdminSessionChange extends base
{
    protected $supportedNames = [
        'imageView',
    ];

    /**
     * @since ZC v2.0.0
     */
    public function change()
    {
        // -----
        // Deny access unless running under the admin.
        //
        if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
            return '';
        }

        // -----
        // Give an observer the opportunity to add other supported names.  Each
        // name can contain *only* alphanumeric characters.
        //
        $other_names = [];
        $this->notify('NOTIFY_AJAX_ADMIN_NOTIFICATIONS', '', $other_names);
        foreach ($other_names as $name) {
            if (ctype_alnum((string)$name) === true) {
                $this->supportedNames[] = (string)$name;
            }
        }

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
