<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger;

use Psr\Log\InvalidArgumentException;

class LoggerHandler
{
    protected array $options = [];

    /**
     * Constructor for the LoggerHandler class.
     *
     * @param array $commonOptions An array containing the common options for the LoggerHandler.
     *                             Must contain the 'channel' and 'prefix' keys.
     * @throws \Exception If the common options array is empty or not an array.
     * @throws \Exception If the common options array does not contain the 'channel' or 'prefix' keys.
     */
    public function __construct(array $commonOptions)
    {
        if (empty($commonOptions)) {
            throw new InvalidArgumentException('Common options for LoggerHandler not set');
        }
        if (!is_array($commonOptions)) {
            throw new InvalidArgumentException('Common options for LoggerHandler must be an array');
        }
        if (!isset($commonOptions['channel'])) {
            throw new \Exception('Common options for LoggerHandler must contain a channel');
        }
        if (!isset($commonOptions['prefix'])) {
            throw new InvalidArgumentException('Common options for LoggerHandler must contain a prefix');
        }
        $this->options = $commonOptions;
    }
}
