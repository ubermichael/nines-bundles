<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class NinesMakerBundle extends AbstractBundle {
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder) : void {
        $container->import('../config/services.yaml');
        if ('test' === $container->env()) {
            $container->import('../config/services_test.yaml');
        }
    }
}
