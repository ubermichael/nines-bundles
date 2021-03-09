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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends Command {
    public const BATCH_SIZE = 250;

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
        $this->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Batch size', self::BATCH_SIZE);
        $this->addArgument('classes', InputArgument::IS_ARRAY, 'Classes to index');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $batch = $input->getOption('batch');
        $classes = array_map(function($s) {
            return (strpos($s, '\\') === false ? 'App\\Entity\\' . $s : $s);
        }, $input->getArgument('classes'));

        $n = 0;
        $client = $this->builder->build();
        $update = $client->createUpdate();
        foreach ($this->mapper->getMappedClasses() as $class) {
            $output->writeln($class);
            if($classes && ! in_array($class, $classes)) {
                $output->writeln("skipped");
                continue;
            }
            $iterator = $this->em->createQuery("SELECT e FROM {$class} e")->iterate();

            foreach ($iterator as $row) {
                $n++;
                $mapped = $this->mapper->mapEntity($row[0]);
                if( ! $mapped) {
                    dump($row[0]);
                    exit;
                }
                $doc = $update->createDocument($mapped);
                $update->addDocument($doc);
                if (0 === $n % $batch) {
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
