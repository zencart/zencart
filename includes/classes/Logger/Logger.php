<?php

namespace Zencart\Logger;

use Monolog\Logger as MonologLogger;

abstract class Logger
{
    protected array $options;
    protected MonologLogger $logger;

    public function __construct (array $options)
    {
        $this->options = $options;
        $loggerChannel = isset($this->options['channel']) ? $this->options['channel'] . '-logger' : 'default-logger';
        $this->logger = new MonologLogger($loggerChannel);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public  function getMonologLogger(): MonologLogger
    {
        return $this->logger;
    }
}
