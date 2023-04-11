<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker\Entity;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Service\Attribute\Required;

class MakeFixture extends AbstractEntityMaker {
    private int $fixtureCount = 4;

    public static function getCommandName() : string {
        return 'nines:make:fixture';
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
        $details['fixture_count'] = $this->fixtureCount;
        $details['metadata'] = $this->metadata;
        $fixtures = $this->twig->render('@NinesMaker/data_fixture/fixture.php.twig', $details);
        $generator->dumpFile($details['fixture_class_path'], $fixtures);
        $generator->writeChanges();
    }

    #[Required]
    public function setParameterBag(ParameterBagInterface $parameterBag) : void {
        $this->fixtureCount = $parameterBag->get('nines.maker.fixture_count');
    }
}
