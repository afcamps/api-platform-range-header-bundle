<?php

/*
 * This file is part of the API Platform range header pagination Bundle.
 *
 * (c) Campings.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination;

use ApiPlatform\State\Pagination\PaginatorInterface;

final class RangePaginator implements \IteratorAggregate, PaginatorInterface, Countable
{
    private array $results;
    private ?int $start;
    private ?int $end;
    private ?int $totalItems;

    public function __construct(array $results, ?int $start, ?int $end, ?int $totalItems)
    {
        $this->results = $results;
        $this->start = $start;
        $this->end = $end;
        $this->totalItems = $totalItems;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->results);
    }

    public function count(): int
    {
        return $this->totalItems;
    }

    public function start(): ?int
    {
        return $this->start;
    }

    public function end(): ?int
    {
        return $this->end;
    }

    public function getLastPage(): float
    {
        return $this->end;
    }

    public function totalItems(): ?int
    {
        return $this->totalItems;
    }

    public function getCurrentPage(): float
    {
        return $this->start;
    }

    public function getItemsPerPage(): float
    {
        return $this->end - $this->start;
    }

    public function getTotalItems(): float
    {
        return $this->totalItems ?? 0;
    }
}
