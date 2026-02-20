<?php declare(strict_types=1);
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

namespace Zencart\ViewBuilders;

use ArrayObject;
use IteratorAggregate;
use Traversable;

class NativePaginator implements IteratorAggregate
{
    /** @var ArrayObject<int, ArrayObject> */
    protected ArrayObject $collection;

    public function __construct(
        array $items,
        protected int $total,
        protected int $perPage,
        protected int $currentPage,
        protected string $pageName = 'page'
    ) {
        $wrapped = [];
        foreach ($items as $item) {
            $wrapped[] = new ArrayObject((array)$item, ArrayObject::ARRAY_AS_PROPS);
        }
        $this->collection = new ArrayObject($wrapped);
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function firstItem(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return (($this->currentPage - 1) * $this->perPage) + 1;
    }

    public function lastItem(): int
    {
        if ($this->total === 0) {
            return 0;
        }
        return min($this->currentPage * $this->perPage, $this->total);
    }

    public function getCollection(): ArrayObject
    {
        return $this->collection;
    }

    public function getIterator(): Traversable
    {
        return $this->collection->getIterator();
    }
}
