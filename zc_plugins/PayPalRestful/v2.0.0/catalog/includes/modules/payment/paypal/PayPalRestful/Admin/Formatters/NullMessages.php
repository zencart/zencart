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
    public array $messages = [];
    public array $errors = [];
    public int $size = 0;
    private array $formats = [];


    public function __construct()
    {
        // Do nothing. This is a null-object class.
    }

    public function add($class = '', $message = '', $type = 'error'): void
    {
    }

    public function add_session($class = '', $message = '', $type = 'error'): void
    {
    }

    public function add_from_session(): void
    {
    }

    public function size($key): int
    {
        return 0;
    }

    public function clear(): void
    {
    }

    public function reset(): void
    {
    }

    public function output($class = ''): string
    {
        return '';
    }

    public function getMessages(): array
    {
        return [];
    }

    public function setMessageFormatting($formattingArray = []): void
    {
    }

    public function getDefaultFormats(): array
    {
        return [];
    }
}
