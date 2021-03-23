<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeDebug extends AbstractNinesMaker
{
    public static function getCommandName() : string {
        return 'nines:make:debug';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->setDescription('Dump metadata Doctrine entity class');
        $command->addArgument('name', InputArgument::IS_ARRAY, 'The class name of the entity.');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        foreach ($input->getArgument('name') as $name) {
            $params = $this->collect($generator, $name);
            dump($params);
        }
    }
}
