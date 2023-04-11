<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * Generates the configuration tree builder.
     */
    public function getConfigTreeBuilder() : TreeBuilder {
        $builder = new TreeBuilder('nines_solr');
        $builder->getRootNode()
            ->children()
            ->booleanNode('enabled')->defaultFalse()->end()
            ->scalarNode('url')->defaultNull()->end()
            ->scalarNode('page_size')->defaultNull()->end()
            ->end();

        return $builder;
    }
}
