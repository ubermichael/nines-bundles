<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see https://symfony.com/doc/3.4/bundles/extension.html
 */
class NinesFeedbackExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container) : void {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('nines_feedback.default_status', $config['default_status']);
        $container->setParameter('nines_feedback.public_status', $config['public_status']);
        $container->setParameter('nines_feedback.sender', $config['sender']);
        $container->setParameter('nines_feedback.subject', $config['subject']);
        $container->setParameter('nines_feedback.recipients', $config['recipients']);

        $map = [];

        foreach ($config['routing'] as $routing) {
            $map[$routing['class']] = $routing['route'];
        }
        $container->setParameter('nines_feedback.routing', $map);
    }
}
