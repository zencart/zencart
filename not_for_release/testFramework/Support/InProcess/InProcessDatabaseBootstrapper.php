<?php

namespace Tests\Support\InProcess;

use RuntimeException;

class InProcessDatabaseBootstrapper
{
    public function bootstrap(string $context): void
    {
        $script = ROOTCWD . 'not_for_release/testFramework/Support/InProcess/bootstrap_database.php';
        $phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';
        $command = escapeshellarg($phpBinary) . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($context) . ' 2>&1';

        $output = [];
        $status = 0;
        exec($command, $output, $status);

        if ($status !== 0) {
            throw new RuntimeException(
                "Isolated in-process database bootstrap failed.\n" . implode(PHP_EOL, $output)
            );
        }
    }
}
