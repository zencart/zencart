<?php
/**
 * A null-object class that simulates messageStack messages triggered by webhooks in code that is shared by the
 * admin_notifications method of the PayPal Restful payment module.
 *
 * @copyright Copyright 2025 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2023 Nov 16 Modified in v2.0.0 $
 *
 * Last updated: v1.2.2
 */

namespace PayPalRestful\Admin\Formatters;

class NullMessages
{
    public $messages = [];
    public $errors = [];
    public $size = 0;
    private $formats = [];


    public function __construct()
    {
        // Do nothing. This is a null-object class.
    }

    public function add($class = '', $message = '', $type = 'error')
    {
    }

    public function add_session($class = '', $message = '', $type = 'error')
    {
    }

    public function add_from_session()
    {
    }

    public function size($key)
    {
        return 0;
    }

    public function clear()
    {
    }

    public function reset()
    {
    }

    public function output($class = '')
    {
        return '';
    }

    public function getMessages()
    {
        return [];
    }

    public function setMessageFormatting($formattingArray = [])
    {
    }

    public function getDefaultFormats()
    {
        return [];
    }
}

