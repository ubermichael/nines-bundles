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

class MakeTemplates extends AbstractEntityMaker {
    private function mapping() : array {
        return [
            '@NinesMaker/templates/index.html.twig.twig' => 'index.html.twig',
            '@NinesMaker/templates/show.html.twig.twig' => 'show.html.twig',
            '@NinesMaker/templates/new.html.twig.twig' => 'new.html.twig',
            '@NinesMaker/templates/edit.html.twig.twig' => 'edit.html.twig',
            '@NinesMaker/templates/search.html.twig.twig' => 'search.html.twig',
            '@NinesMaker/templates/partial/list.html.twig.twig' => 'partial/list.html.twig',
            '@NinesMaker/templates/partial/table.html.twig.twig' => 'partial/table.html.twig',
            '@NinesMaker/templates/partial/form.html.twig.twig' => 'partial/form.html.twig',
            '@NinesMaker/templates/partial/detail.html.twig.twig' => 'partial/detail.html.twig',
        ];
    }

    public static function getCommandName() : string {
        return 'nines:make:templates';
    }

    public static function getCommandDescription() : string {
        return '';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->addArgument(
            'name',
            InputArgument::REQUIRED,
            'Class name to generate',
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        $name = $input->getArgument('name');
        $details = $this->metadata->getDetails($name);
        if ( ! class_exists($details['entity_class_name'])) {
            $io->error("Class {$details['entity_class_name']} does not exist.");

            return;
        }
        $templatesPath = dirname($details['controller_class_path'], 3) . '/templates/' . $details['route_name_prefix'];

        foreach ($this->mapping() as $templateName => $path) {
            $data = $this->twig->render($templateName, $details);
            $generator->dumpFile($templatesPath . '/' . $path, $data);
        }
        $generator->writeChanges();
    }
}
