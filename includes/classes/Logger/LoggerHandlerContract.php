<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger;

interface LoggerHandlerContract
{
    public  function setup(Logger $logger): void;
}
