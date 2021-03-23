<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $copyFields;

    /**
     * @var null|EntityMapper
     */
    private $mapper;

    protected static $defaultName = 'nines:solr:schema';

    public function __construct(EntityMapperBuilder $builder) {
        parent::__construct(self::$defaultName);
        $this->mapper = $builder->build();
    }

    protected function configure() : void {
        $this->setDescription('Show the solr schema.');
        $this->addArgument('classes', InputArgument::IS_ARRAY, 'List of classes to map');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
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
            $table = new Table($output);
            $table->setHeaders(['name', 'field', 'getter', 'mutator', 'filters']);

            foreach ($entityMeta->getFieldMetadata() as $fieldMeta) {
                $row = [
                    $fieldMeta->getSolrName() . ($fieldMeta->getBoost() ? '^' . $fieldMeta->getBoost() : ''),
                    $fieldMeta->getFieldName(),
                    $fieldMeta->getGetter(),
                    $fieldMeta->getMutator(),
                    implode(',', $fieldMeta->getFilters()),
                ];
                $table->addRow($row);
            }
            $table->render();
        }

        return 0;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    public function setCopyFields($copyFields) : void {
        $this->copyFields = $copyFields;
    }
}
