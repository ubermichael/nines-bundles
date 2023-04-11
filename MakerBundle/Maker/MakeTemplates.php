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

class MakeTemplates extends AbstractNinesMaker {
    /**
     * @param array<string,string> $params
     *
     * @return array<string,string>
     */
    private function mapping(array $params) : array {
        return [
            '@NinesMaker/templates/index.html.twig.twig' => 'templates/' . $params['templates_path'] . '/index.html.twig',
            '@NinesMaker/templates/show.html.twig.twig' => 'templates/' . $params['templates_path'] . '/show.html.twig',
            '@NinesMaker/templates/new.html.twig.twig' => 'templates/' . $params['templates_path'] . '/new.html.twig',
            '@NinesMaker/templates/new_popup.html.twig.twig' => 'templates/' . $params['templates_path'] . '/new_popup.html.twig',
            '@NinesMaker/templates/edit.html.twig.twig' => 'templates/' . $params['templates_path'] . '/edit.html.twig',
            '@NinesMaker/templates/search.html.twig.twig' => 'templates/' . $params['templates_path'] . '/search.html.twig',
            '@NinesMaker/templates/partial/table.html.twig.twig' => 'templates/' . $params['templates_path'] . '/partial/table.html.twig',
            '@NinesMaker/templates/partial/form.html.twig.twig' => 'templates/' . $params['templates_path'] . '/partial/form.html.twig',
            '@NinesMaker/templates/partial/detail.html.twig.twig' => 'templates/' . $params['templates_path'] . '/partial/detail.html.twig',
        ];
    }

    public static function getCommandName() : string {
        return 'nines:make:templates';
    }

    public static function getCommandDescription() : string {
        return 'Generate twig templates based on a doctrine entity';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->setDescription('Creates CRUD templates for Doctrine entity class');
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
            if ( ! class_exists($params['entity_full_class_name'])) {
                $io->warning('Class not found: ' . $params['entity_full_class_name']);

                continue;
            }

            foreach ($this->mapping($params) as $src => $dst) {
                $data = $this->twig->render($src, $params);
                $generator->dumpFile($dst, $data);
            }
        }
        $generator->writeChanges();
    }
}
