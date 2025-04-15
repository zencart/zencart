<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license https://opensource.org/license/mit MIT License
 * @version 
 */

namespace Zencart\Logger\Handlers;

use Zencart\Logger\LoggerHandler;
use Zencart\Logger\LoggerHandlerContract;
use Monolog\Handler\StreamHandler;

class FileLoggerHandler extends LoggerHandler implements LoggerHandlerContract
{

    public function setup($logger): void
    {
        $debugLogFile = $this->getDebugLogFile();
        $logger->getMonologLogger()->pushHandler(new StreamHandler($debugLogFile));
    }

    protected function getDebugLogFile(): string
    {
        if (IS_ADMIN_FLAG === false) {
            $logfile_suffix = 'c-' . ($_SESSION['customer_id'] ?? 'na') . '-' . substr($_SESSION['customer_first_name'] ?? 'na', 0, 3) . substr($_SESSION['customer_last_name'] ?? 'na', 0, 3);
        } else {
            $logfile_suffix = 'adm-a' . ($_SESSION['admin_id'] ?? 'na');
            global $order;
            if (isset($order)) {
                $logfile_suffix .= '-o' . $order->info['order_id'];
            }
        }
        $debugLogFile = DIR_FS_LOGS . '/' . $this->options['channel'] . '-'. $this->options['prefix'] . '-' . $logfile_suffix . '-' . date('Ymd') . '.log';
        return $debugLogFile;

    }
}
