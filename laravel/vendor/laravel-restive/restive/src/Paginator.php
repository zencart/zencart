<?php declare(strict_types=1);

namespace Restive;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Restive\Http\Requests\Request;

class Paginator
{
    protected int $limit;
    public function __construct()
    {
        $this->limit = 10;
    }

    public function paginate(Builder $query, Request $request): Collection|LengthAwarePaginator
    {
        $this->setPaginationParameters($request);
        if ($request->input('paginate', 'yes') === 'no') {
            return $query->get();
        }
        return $query->paginate($this->limit);
    }

    protected function setPaginationParameters(Request $request): void
    {
        $this->limit = $request->input('limit', $this->limit);
    }
}