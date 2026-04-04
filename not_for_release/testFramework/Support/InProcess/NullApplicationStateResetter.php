<?php

namespace Tests\Support\InProcess;

class NullApplicationStateResetter implements ApplicationStateResetter
{
    public function reset(): void
    {
    }
}
