<?php

declare(strict_types=1);

namespace Setono\SyliusReserveStockPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    private const DEFAULT_TTL = 3600;

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('setono_sylius_reserve_stock');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('setono_sylius_reserve_stock');
        }
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->integerNode('ttl')
                    ->defaultValue(self::DEFAULT_TTL)
                    ->example(1800)
                    ->info('Define the Time To Live (TTL) in seconds for a product reservation. Setting to 0 disables this check, all carts will be taken in account for an indefinite period.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
