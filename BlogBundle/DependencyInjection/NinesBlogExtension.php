<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\DependencyInjection;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NinesBlogExtension extends Extension {
    /**
     * Loads a specific configuration.
     *
     * @param array<mixed> $configs
     *
     * @throws Exception
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container) : void {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('nines_blog.excerpt_length', $config['excerpt_length']);
        $container->setParameter('nines_blog.homepage_posts', $config['homepage_posts']);
        $container->setParameter('nines_blog.menu_posts', $config['menu_posts']);
        $container->setParameter('nines_blog.default_status', $config['default_status']);
        $container->setParameter('nines_blog.default_category', $config['default_category']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.yaml');
    }
}
