<?php

namespace Tests\Support\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;

trait CrossConcerns
{
    public function detectUser()
    {
        $user = $_SERVER['USER'] ?? $_SERVER['MY_USER'];
        if (defined('GITLAB_CI')) {
            $user = 'runner';
        }
        return $user;
    }
}
