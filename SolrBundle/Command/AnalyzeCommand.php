<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Client\ClientBuilder;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
use Solarium\QueryType\Update\Query\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyzeCommand extends Command
{
    private $builder;

    private $mapper;

    private $em;

    protected static $defaultName = 'nines:solr:analyze';

    public function __construct(ClientBuilder $builder, EntityMapperBuilder $mapperBuilder) {
        parent::__construct();
        $this->builder = $builder;
        $this->mapper = $mapperBuilder->build();
    }

    protected function configure() : void {
        $this->setDescription('Clear the index.');
        $this->addArgument('class', InputArgument::REQUIRED, 'Class of the entity to dump');
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of the entity to dump');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $client = $this->builder->build();
        $query = $client->createAnalysisDocument();
        $query->setShowMatch(true);

        $class = $input->getArgument('class');
        if (false === mb_strpos($class, '\\')) {
            $class = 'App\\Entity\\' . $class;
        }
        $id = $input->getArgument('id');
        $entity = $this->em->find($class, $id);
        if ( ! $entity) {
            $output->writeln('Entity not found.');
        }

        $data = $this->mapper->mapEntity($entity);
        $doc = new Document($data);
        $query->addDocument($doc);
        $result = $client->analyze($query);

        foreach ($result as $document) {
            $output->writeln('Document: ' . $document->getName());

            foreach ($document as $field) {
                $output->writeln('  Field: ' . $field->getName());

                foreach ($field->getIndexAnalysis() as $classes) {
                    $output->writeln('    Class: ' . $classes->getName());

                    foreach ($classes as $result) {
                        $output->writeln('      ' . $result->getType() . ':' . $result->getText());
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
