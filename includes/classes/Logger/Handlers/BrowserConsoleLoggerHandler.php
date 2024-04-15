<?php

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
