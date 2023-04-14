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

class MakeServiceTest extends AbstractServiceMaker {
    public static function getCommandName() : string {
        return 'nines:make:service-test';
    }

    public static function getCommandDescription() : string {
        return 'Generate a service test stub';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->addArgument('name', InputArgument::REQUIRED, 'Class name to of the service which needs tests');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        $serviceDetails = $generator->createClassNameDetails($input->getArgument('name'), 'Service\\');
        $serviceName = $serviceDetails->getFullName();
        if ( ! class_exists($serviceName)) {
            $io->warning("Service class {$serviceName} does not exist.");
            $answer = $io->confirm('Are you sure?', false);
            if ( ! $answer) {
                return;
            }
        }
        $inBundle = ! str_starts_with($serviceName, 'App\\');

        $testDetails = $generator->createClassNameDetails($input->getArgument('name') . 'Test', 'Test\\Service', 'Test');
        $testName = $testDetails->getFullName();
        if ($inBundle) {
            $prefix = Str::removeSuffix($testName, $testDetails->getRelativeName());
            $testName = $prefix . 'Test\\' . $testDetails->getRelativeName();
            $testDetails = $generator->createClassNameDetails('\\' . $testName, $prefix);
        }
        $params = [
            'ns' => Str::removeSuffix($testName, '\\' . $testDetails->getShortName()),
            'serviceName' => $serviceName,
            'serviceDetails' => $serviceDetails,
            'testName' => $testName,
            'testDetails' => $testDetails,
        ];

        if ( ! class_exists($serviceName)) {
            $io->warning("{$serviceName} does not exist.");
            $answer = $io->confirm('Are you sure?', false);
            if ( ! $answer) {
                return;
            }
        }

        if (class_exists($testName)) {
            $io->warning("{$testName} already exists and will be overwritten.");
            $answer = $io->confirm('Are you sure?', false);
            if ( ! $answer) {
                return;
            }
        }

        $path = $this->fileManager->getRelativePathForFutureClass($testName);
        if ( ! $path) {
            $io->error("Cannot determine path for {$testName}.");

            return;
        }

        $data = $this->twig->render('@NinesMaker/test/service.php.twig', $params);
        $generator->dumpFile($path, $data);
        $generator->writeChanges();
    }
}
