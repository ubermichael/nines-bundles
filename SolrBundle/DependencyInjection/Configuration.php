<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder() {
        $builder = new TreeBuilder('nines_solr');
        $builder->getRootNode()
            ->children()
            ->scalarNode('host')->defaultValue('127.0.0.1')->end()
            ->scalarNode('port')->defaultValue(8983)->end()
            ->scalarNode('path')->defaultNull('/')->end()
            ->scalarNode('core')->defaultNull('solr')->end()
            ->arrayNode('copy_fields')->prototype('array')->children()
            ->scalarNode('from')->end()
            ->scalarNode('to')->end()
            ->end()->end()
            ->end()
            ->end()
        ;

        return $builder;
    }
}
