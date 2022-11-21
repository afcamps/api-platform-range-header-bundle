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

namespace Campings\Bundle\ApiPlatformRangeHeaderBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('api_platform_range_header');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->children()
                        ->booleanNode('range_header_enabled')
                            ->defaultTrue()
                        ->end()
                        ->scalarNode('range_unit')
                            ->defaultValue('items')
                        ->end()
                        ->booleanNode('count_total_items')
                            ->defaultTrue()
                        ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
