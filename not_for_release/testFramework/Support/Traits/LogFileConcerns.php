<?php

namespace Tests\Support\Traits;

use Tests\Support\TestFrameworkFilesystem;

trait LogFileConcerns
{
    public function logFilesExists(): array
    {
        return (new TestFrameworkFilesystem())->listDebugLogFiles(DIR_FS_CATALOG);
    }
}
