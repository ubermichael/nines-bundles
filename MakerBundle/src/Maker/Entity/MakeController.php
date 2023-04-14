<?php

declare(strict_types=1);

namespace Nines\MakerBundle\Maker\Entity;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeController extends AbstractEntityMaker {
    public static function getCommandName() : string {
        return 'nines:make:controller';
    }

    public static function getCommandDescription() : string {
        return '';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->addArgument('name', InputArgument::REQUIRED, 'Class name to generate');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        $name = $input->getArgument('name');
        $details = $this->metadata->getDetails($name);
        if ( ! class_exists($details['entity_class_name'])) {
            $io->error("Class {$details['entity_class_name']} does not exist.");

            return;
        }
        $controller = $this->twig->render('@NinesMaker/controller/controller.php.twig', $details);
        $generator->dumpFile($details['controller_class_path'], $controller);
        $generator->writeChanges();
    }
}
