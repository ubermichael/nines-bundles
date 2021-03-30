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
use Solarium\Client;
use Solarium\QueryType\Update\Query\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a document from an entity and pass it to Solr for analysis, and then
 * display the result.
 */
class AnalyzeCommand extends Command
{
    /**
     * @var EntityMapper
     */
    private $mapper;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private Client $client;

    protected static $defaultName = 'nines:solr:analyze';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Clear the index.');
        $this->addArgument('class', InputArgument::REQUIRED, 'Class of the entity to dump');
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of the entity to dump');
    }

    /**
     * Executes the command. Return 0 for success and non-zero for failure.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $query = $this->client->createAnalysisDocument();
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

        $doc = $this->mapper->toDocument($entity);
        $query->addDocument($doc);
        $result = $this->client->analyze($query);

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
    public function setClient(Client $client) : void {
        $this->client = $client;
    }

    /**
     * @required
     */
    public function setEntityMapper(EntityMapper $mapper) : void {
        $this->mapper = $mapper;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
