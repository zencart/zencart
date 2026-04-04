<?php

namespace Tests\Support\InProcess;

interface ApplicationStateResetter
{
    public function reset(): void;
}
