<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * Generates the configuration tree builder.
     */
    public function getConfigTreeBuilder() : TreeBuilder {
        $builder = new TreeBuilder('nines_media');
        $builder
            ->getRootNode()
            ->children()
            ->scalarNode('root')->defaultValue('data')->end()
            ->scalarNode('thumb_width')->defaultValue(450)->end()
            ->scalarNode('thumb_height')->defaultValue(350)->end()
            ->end();

        return $builder;
    }
}
