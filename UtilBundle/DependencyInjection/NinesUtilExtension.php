<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\DependencyInjection;

use Exception;
use InvalidArgumentException;
use Nines\UtilBundle\Services\Text;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class NinesUtilExtension extends Extension {
    /**
     * Loads a specific configuration.
     *
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $definition = $container->getDefinition(Text::class);
        $definition->replaceArgument('$defaultTrimLength', $config['trim_length']);

        $map = [];

        foreach ($config['routing'] as $routing) {
            $map[$routing['class']] = $routing['route'];
        }
        $container->setParameter('nines_util.routing', $map);
    }
}
