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

interface Countable
{
    public function totalItems(): ?int;

    public function start(): ?int;

    public function end(): ?int;
}
