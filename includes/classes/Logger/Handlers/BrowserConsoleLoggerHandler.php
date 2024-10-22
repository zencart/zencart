<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger\Handlers;

use Monolog\Handler\BrowserConsoleHandler;
use Zencart\Logger\Logger;
use Zencart\Logger\LoggerHandler;
use Zencart\Logger\LoggerHandlerContract;

class BrowserConsoleLoggerHandler  extends LoggerHandler implements LoggerHandlerContract
{

    public function setup(Logger $logger): void
    {
        $logger->getMonologLogger()->pushHandler(new BrowserConsoleHandler());
    }
}
