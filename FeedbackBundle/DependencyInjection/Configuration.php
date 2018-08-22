<?php

namespace Nines\FeedbackBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface {

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('nines_feedback');
        $rootNode
            ->children()
                ->scalarNode('default_status')->end()
                ->scalarNode('public_status')->end()
                ->arrayNode('commenting')
                    ->prototype('array')
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('route')->end()
                    ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
