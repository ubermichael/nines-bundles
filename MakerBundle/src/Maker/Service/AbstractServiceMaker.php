<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker\Service;

use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Twig\Environment;

abstract class AbstractServiceMaker extends AbstractMaker {
    protected Environment $twig;

    protected FileManager $fileManager;

    public function __construct(Environment $twig, FileManager $fileManager) {
        $this->twig = $twig;
        $this->fileManager = $fileManager;
    }

    public function configureDependencies(DependencyBuilder $dependencies) : void {
    }
}
