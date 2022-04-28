<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
        $treeBuilder = new TreeBuilder('nines_feedback');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->scalarNode('default_status')->defaultNull()->end()
            ->scalarNode('public_status')->defaultNull()->end()
            ->scalarNode('sender')->defaultNull()->end()
            ->scalarNode('subject')->defaultNull()->end()
            ->variableNode('recipients')->end()
            ->end();

        return $treeBuilder;
    }
}
