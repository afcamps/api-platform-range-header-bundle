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

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\DataProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\State\Pagination\RangePaginator;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\Entity\Dummy;

class DummyProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $dummies = [];
        for ($i = 0; $i < 100; ++$i) {
            $dummies[] = new Dummy("Test $i", "Test content $i");
        }

        $start = $context['filters']['range_start'] ?? null;
        $end = $context['filters']['range_end'] ?? null;

        $parts = $dummies;

        if (null !== $start && null !== $end) {
            $start = (int) $start;
            $end = (int) $end;
            $parts = \array_slice($dummies, $start, $end - $start);
        }

        return new RangePaginator($parts, $start, $end, \count($dummies));
    }
}
