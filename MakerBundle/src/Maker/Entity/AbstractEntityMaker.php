<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker\Entity;

use Nines\MakerBundle\Service\Metadata;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Twig\Environment;

abstract class AbstractEntityMaker extends AbstractMaker {
    protected Environment $twig;

    protected Metadata $metadata;

    public function __construct(Environment $twig, Metadata $metadata) {
        $this->twig = $twig;
        $this->metadata = $metadata;
    }

    public function configureDependencies(DependencyBuilder $dependencies) : void {
    }
}
