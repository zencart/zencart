<?php declare(strict_types=1);

namespace Restive;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Restive\Http\Requests\Request;

class Paginator
{
    protected int $limit;

    public function paginate(Builder $query, Request $request): Collection|LengthAwarePaginator
    {
        $this->setPaginationParameters($request);
        return $query->paginate($this->limit);
    }

    protected function setPaginationParameters(Request $request): void
    {
        $paginationSafety = (int)config('restive.pagination_safety', false);
        $paginationLimit = (int)config('restive.pagination_limit', 10);

        $requestedLimit = (int)$request->input('limit', 10);

        $this->limit = $paginationSafety
            ? min($requestedLimit, $paginationLimit)
            : $requestedLimit;

    }
}