<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeMenu extends AbstractNinesMaker {
    /**
     * {@inheritdoc}
     */
    public static function getCommandName() : string {
        return 'nines:make:menu';
    }

    public static function getCommandDescription() : string {
        return 'Generate stub menu';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->setDescription('Creates or updates an entity.');
        $command->addArgument('name', InputArgument::IS_ARRAY, 'The class name of the entity.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        $names = $input->getArgument('name');
        $data = $this->twig->render('@NinesMaker/menu/builder.php.twig', ['names' => $names]);
        $generator->dumpFile('src/Menu/Builder.php', $data);
        $generator->writeChanges();
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command) : void {
        // TODO: Implement interact() method.
    }
}
