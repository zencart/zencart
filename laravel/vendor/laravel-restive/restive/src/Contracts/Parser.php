<?php

namespace Restive\Contracts;

use Illuminate\Database\Eloquent\Builder;
interface Parser
{
    public function buildQuery(Builder $query) : Builder;
}
