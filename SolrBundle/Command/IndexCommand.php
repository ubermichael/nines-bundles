<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Client\Builder;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends Command {
    public const BATCH_SIZE = 1000;

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var null|EntityMapper
     */
    private $mapper;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected static $defaultName = 'nines:solr:index';

    public function __construct(Builder $builder, EntityMapperBuilder $mapperBuilder) {
        parent::__construct();
        $this->builder = $builder;
        $this->mapper = $mapperBuilder->build();
    }

    protected function configure() : void {
        $this->setDescription('Index the data.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $client = $this->builder->build();
        $n = 0;
        $update = $client->createUpdate();

        foreach ($this->mapper->getMappedClasses() as $class) {
            $iterator = $this->em->createQuery("SELECT e FROM {$class} e")->iterate();

            foreach ($iterator as $row) {
                $n++;
                $mapped = $this->mapper->mapEntity($row[0]);
                $doc = $update->createDocument($mapped);
                $update->addDocument($doc);
                if (0 === $n % self::BATCH_SIZE) {
                    $update->addCommit();
                    $result = $client->update($update);
                    $output->writeln("{$n}: " . $result->getResponse()->getStatusMessage() . ' ' . $result->getQueryTime() . 'ms');
                    $update = $client->createUpdate();
                    $this->em->clear();
                }
            }
        }
        $update->addCommit();
        $result = $client->update($update);
        $output->writeln("{$n}: " . $result->getResponse()->getStatusMessage() . ' ' . $result->getQueryTime() . 'ms');
        $this->em->clear();

        return 0;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
