<?php
namespace Zencart\Logger;

interface LoggerHandlerContract
{
    public  function setup(Logger $logger): void;
}
