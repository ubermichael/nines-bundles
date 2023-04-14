<?php

declare(strict_types=1);

namespace Nines\MakerBundle\Maker\Service;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeMaker extends AbstractServiceMaker {
    public static function getCommandName() : string {
        return 'nines:make:maker';
    }

    public static function getCommandDescription() : string {
        return 'Generate a maker stub';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->addArgument('name', InputArgument::REQUIRED, 'Class name to generate');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        $makerDetails = $generator->createClassNameDetails($input->getArgument('name'), 'Maker\\');
        $fqcn = $makerDetails->getFullName();
        if (class_exists($makerDetails->getFullName())) {
            $io->warning("{$fqcn} already exists and will be overwritten.");
            $answer = $io->confirm('Are you sure?', false);
            if ( ! $answer) {
                return;
            }
        }
        $io->writeln("Generating {$fqcn}");
        $path = $this->fileManager->getRelativePathForFutureClass($fqcn);
        if ( ! $path) {
            $io->error("Cannot determine path for {$fqcn}.");

            return;
        }
        $parts = explode('\\', $fqcn);
        $end = preg_replace('/([a-z])([A-Z])/', '$1:$2', end($parts));
        $name = mb_strtolower("{$parts[0]}:{$end}");
        $data = $this->twig->render('@NinesMaker/maker/maker.php.twig', [
            'ns' => Str::removeSuffix($fqcn, '\\' . $makerDetails->getShortName()),
            'name' => $name,
            'className' => $fqcn,
            'shortName' => $makerDetails->getShortName(),
        ]);

        $generator->dumpFile($path, $data);
        $generator->writeChanges();
    }
}
