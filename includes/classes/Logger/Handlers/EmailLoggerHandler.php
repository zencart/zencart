<?php

namespace Zencart\Logger\Handlers;

use Monolog\Handler\NativeMailerHandler;
use Zencart\Logger\LoggerHandler;
use Zencart\Logger\LoggerHandlerContract;

class EmailLoggerHandler extends LoggerHandler implements LoggerHandlerContract
{
    public function setup($logger): void
    {
        $logger->getMonologLogger()->pushHandler(new NativeMailerHandler('foo@bar.com', 'Zencart Debug Log', 'Zencart Debug'));
    }
}
