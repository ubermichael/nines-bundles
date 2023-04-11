<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Nines\SolrBundle\Mapper\EntityMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show a description of the Solr search schema.
 */
class SchemaCommand extends Command {
    private ?EntityMapper $mapper = null;

    protected static $defaultName = 'nines:solr:schema';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Show the solr schema.');
        $this->addArgument('classes', InputArgument::IS_ARRAY, 'List of classes to map');
    }

    /**
     * Execute the command. Returns 0 for success.
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $classes = $input->getArgument('classes');
        if ( ! $classes) {
            $classes = $this->mapper->getClasses();
        }

        foreach ($classes as $class) {
            if (false === mb_strpos($class, '\\')) {
                $class = 'App\\Entity\\' . $class;
            }
            $output->writeln($class);
            $entityMeta = $this->mapper->getEntityMetadata($class);
            if ( ! $entityMeta) {
                $output->writeln('Not mapped');

                continue;
            }
            $idMeta = $entityMeta->getId();
            $output->writeln('  id => ' . $class . ':' . $idMeta->getGetter());

            foreach ($entityMeta->getFixed() as $k => $v) {
                $output->writeln("  {$k} => {$v}");
            }

            foreach ($entityMeta->getCopyFields() as $copyField) {
                $output->writeln('  ' . $copyField->getSolrName() . ' <= [' . implode(',', $copyField->getFrom()) . ']');
            }
            $table = new Table($output);
            $table->setHeaders(['name', 'field', 'getter', 'mutator', 'filters']);

            foreach ($entityMeta->getFieldMetadata() as $fieldMeta) {
                $row = [
                    $fieldMeta->getSolrName() . ($fieldMeta->getBoost() ? '^' . $fieldMeta->getBoost() : ''),
                    $fieldMeta->getFieldName(),
                    $fieldMeta->getGetter(),
                    $fieldMeta->getMutator(),
                    implode("\n", $fieldMeta->getFilters()),
                ];
                $table->addRow($row);
            }
            $table->render();
        }

        return 0;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityMapper(EntityMapper $mapper) : void {
        $this->mapper = $mapper;
    }
}
