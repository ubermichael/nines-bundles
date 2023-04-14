<?php

declare(strict_types=1);

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
