<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Maker;

use ReflectionException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MakeIndex extends AbstractNinesMaker {
    /**
     * {@inheritdoc}
     */
    public static function getCommandName() : string {
        return 'nines:make:index';
    }

    public static function getCommandDescription() : string {
        return 'Generate Solr index query builder';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->setDescription('Generate Solr index query builder');
        $command->addArgument('name', InputArgument::IS_ARRAY, 'The class name of the entity.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws LoaderError
     * @throws ReflectionException
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        foreach ($input->getArgument('name') as $name) {
            $params = $this->collect($generator, $name);
            if (class_exists($params['index_full_class_name'])) {
                $io->warning("Index {$params['entity_full_class_name']} already exists.");
            }

            $data = $this->twig->render('@NinesMaker/entity/index.php.twig', $params);
            $generator->dumpFile("src/Index/{$params['index_class_name']}.php", $data);
        }
        $generator->writeChanges();
    }
}
