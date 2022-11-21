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

use ApiPlatform\Metadata\Operation;

final class Pagination
{
    /**
     * @var non-empty-array<string, mixed>
     */
    private array $options;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->options = array_merge([
            'range_header_enabled' => true,
            'range_unit' => 'items',
            'range_countable' => true,
            'items_per_page' => 30,
        ], $options);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array<string, mixed> $context
     */
    public function getOffset(Operation $operation = null, array $context = []): int
    {
        return (int) ($context['filters']['range_start'] ?? 0);
    }

    public function getLimit(Operation $operation = null, array $context = []): int
    {
        if (isset($context['filters']['range_end'])) {
            return (int) $context['filters']['range_end'] - $this->getOffset($operation, $context);
        }

        return $this->options['items_per_page'];
    }

    public function getRangeName(Operation $operation = null): string
    {
        return $this->getExtraProperties($operation)['range_unit'] ?? $this->options['range_unit'];
    }

    public function isCountable(Operation $operation = null): bool
    {
        return $this->getExtraProperties($operation)['range_countable'] ?? $this->options['range_countable'];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function rangeHeaderEnabled(Operation $operation = null, array $context = []): bool
    {
        $enabled = $this->options['range_header_enabled'];

        return $this->getExtraProperties($operation)['range_header_enabled'] ?? $enabled;
    }

    private function getExtraProperties(Operation $operation = null): array
    {
        if (null === $operation) {
            return [];
        }

        return $operation->getExtraProperties();
    }
}
