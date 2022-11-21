<?php

declare(strict_types=1);

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\Entity;

use ApiPlatform\Metadata\ApiResource;
use Campings\Bundle\ApiPlatformRangeHeaderBundle\Tests\Application\DataProvider\DummyProvider;

#[ApiResource(
    provider: DummyProvider::class,
    extraProperties: ['range_header_enabled' => true, 'range_unit' => 'dummies']
)]
class Dummy
{
    public string $name;
    public string $content;

    public function __construct(string $name, string $content)
    {
        $this->name = $name;
        $this->content = $content;
    }
}
