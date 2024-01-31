<?php

namespace Tests\Support\Traits;

trait LogFileConcerns
{

    public function logFilesExists()
    {
        $result = glob(DIR_FS_CATALOG . 'logs/myDEBUG*');
        return $result;
    }
}
