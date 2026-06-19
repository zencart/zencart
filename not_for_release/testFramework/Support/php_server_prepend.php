<?php

$originalWorkingDirectory = getcwd();

if ($originalWorkingDirectory !== false) {
    register_shutdown_function(static function () use ($originalWorkingDirectory): void {
        chdir($originalWorkingDirectory);
    });
}

if (!empty($_SERVER['SCRIPT_FILENAME'])) {
    $scriptDirectory = dirname($_SERVER['SCRIPT_FILENAME']);

    if (is_dir($scriptDirectory)) {
        chdir($scriptDirectory);
    }
}
