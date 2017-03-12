<?php

namespace Nines\FeedbackBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class NinesFeedbackExtension extends Extension {

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container) {
		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yml');
		
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $map = array(
            'commenting' => [],
            'flagging' => [],
        );
        $container->setParameter('nines_feedback.default_status', $config['default_status']);
        $container->setParameter('nines_feedback.public_status', $config['public_status']);
        foreach($config['commenting'] as $routing) {
            $map['commenting'][$routing['class']] = $routing['route'];
        }        
        foreach($config['flagging'] as $routing) {
            $map['flagging'][$routing['class']] = $routing['route'];
        }        
        $container->setParameter('nines_feedback.routing', $map);
    }
    
}
