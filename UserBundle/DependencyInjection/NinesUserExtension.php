<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\DependencyInjection;

use Exception;
use InvalidArgumentException;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class NinesUserExtension extends Extension {
    /**
     * Loads a specific configuration.
     *
     * @param array<mixed> $configs
     *
     * @throws Exception
     * @throws InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container) : void {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition(UserManager::class);
        $definition->replaceArgument('$roles', $config['roles']);

        $definition->replaceArgument('$afterLogin', $config['after_login_route']);
        $definition->replaceArgument('$afterRequest', $config['after_request_route']);
        $definition->replaceArgument('$afterReset', $config['after_reset_route']);
        $definition->replaceArgument('$afterLogout', $config['after_logout_route']);
    }
}
