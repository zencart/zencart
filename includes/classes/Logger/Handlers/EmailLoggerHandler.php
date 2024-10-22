<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger\Handlers;

use Monolog\Handler\NativeMailerHandler;
use Zencart\Logger\LoggerHandler;
use Zencart\Logger\LoggerHandlerContract;

class EmailLoggerHandler extends LoggerHandler implements LoggerHandlerContract
{
    public function setup($logger): void
    {
        $logger->getMonologLogger()->pushHandler(new NativeMailerHandler(STORE_OWNER_EMAIL_ADDRESS, 'Zencart Debug Log', 'Zencart Debug'));
    }
}
