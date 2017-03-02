<?php

namespace Nines\FeedbackBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    
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
                ->arrayNode('flagging')
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
