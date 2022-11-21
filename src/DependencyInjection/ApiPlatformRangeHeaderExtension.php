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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ApiPlatformRangeHeaderExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();

        $config = $this->processConfiguration($configuration, $configs);

        /** @var non-empty-array<string, array> $pagination */
        $pagination = $container->getParameter('api_platform.collection.pagination');
        $pagination['range_header_enabled'] = $config['defaults']['range_header_enabled'] ?? true;
        $pagination['range_unit'] = $config['defaults']['range_unit'] ?? 'units';
        $pagination['range_countable'] = $config['defaults']['count_total_items'] ?? true;

        $container->setParameter('api_platform.collection.pagination', $pagination);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config/'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if (class_exists('ApiPlatform\\Core\\Bridge\\Symfony\\Bundle\\ApiPlatformBundle')) {
            $container->prependExtensionConfig('api_platform', [
                'metadata_backward_compatibility_layer' => false,
            ]);
        }
    }
}
