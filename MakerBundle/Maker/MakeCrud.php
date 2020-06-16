<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
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

class MakeCrud extends AbstractNinesMaker {
    private function mapping($params) {
        return [
            '@NinesMaker/controller/controller.php.twig' => 'src/Controller/' . $params['controller_class_name'] . '.php',
            '@NinesMaker/form/form_type.php.twig' => 'src/Form/' . $params['form_class_name'] . '.php',
            '@NinesMaker/templates/index.html.twig.twig' => 'templates/' . $params['templates_path'] . '/index.html.twig',
            '@NinesMaker/templates/show.html.twig.twig' => 'templates/' . $params['templates_path'] . '/show.html.twig',
            '@NinesMaker/templates/new.html.twig.twig' => 'templates/' . $params['templates_path'] . '/new.html.twig',
            '@NinesMaker/templates/new_popup.html.twig.twig' => 'templates/' . $params['templates_path'] . '/new_popup.html.twig',
            '@NinesMaker/templates/edit.html.twig.twig' => 'templates/' . $params['templates_path'] . '/edit.html.twig',
            '@NinesMaker/templates/search.html.twig.twig' => 'templates/' . $params['templates_path'] . '/search.html.twig',
        ];
    }

    public static function getCommandName() : string {
        return 'nines:make:crud';
    }

    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig) : void {
        $command->setDescription('Creates CRUD for Doctrine entity class');
        $command->addArgument('name', InputArgument::IS_ARRAY, 'The class name of the entity.');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator) : void {
        foreach ($input->getArgument('name') as $name) {
            $params = $this->collect($generator, $name);
            if ( ! class_exists($params['entity_full_class_name'])) {
                $io->warning("Class not found: " . $params['entity_full_class_name']);
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
