<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Client\LoggerPlugin;
use Nines\SolrBundle\Mapper\EntityMapper;
use Solarium\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends Command
{
    public const BATCH_SIZE = 250;

    /**
     * @var EntityMapper
     */
    private $mapper;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    private Client $client;

    protected static $defaultName = 'nines:solr:index';

    protected function configure() : void {
        $this->setDescription('Index the data.');
        $this->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Batch size', self::BATCH_SIZE);
        $this->addArgument('classes', InputArgument::IS_ARRAY, 'Classes to index');
        $this->addOption('clear', null, InputOption::VALUE_NONE, 'Clear all entites from the index first');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->client->getPlugin(LoggerPlugin::class)->setOptions(['enabled' => false]);
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $batch = (int) $input->getOption('batch');
        $classes = array_map(function ($s) {
            return false === mb_strpos($s, '\\') ? 'App\\Entity\\' . $s : $s;
        }, $input->getArgument('classes'));

        $n = 0;

        if ($input->hasOption('clear')) {
            $delete = $this->client->createUpdate();
            $delete->addDeleteQuery('*:*');
            $delete->addCommit();
            $this->client->update($delete);
        }

        $update = $this->client->createUpdate();

        foreach ($this->mapper->getClasses() as $class) {
            $output->writeln($class);
            if ($classes && ! in_array($class, $classes, true)) {
                $output->writeln('skipped');
                continue;
            }
            $count = $this->em->createQuery("SELECT count(0) FROM {$class} e")->getOneOrNullResult();
            $iterator = $this->em->createQuery("SELECT e FROM {$class} e")->iterate();

            $progressBar = new ProgressBar($output, (int) $count[1]);

            foreach ($iterator as $row) {
                $n++;
                $doc = $this->mapper->toDocument($row[0]);
                $update->addDocument($doc);
                if (0 === $n % $batch) {
                    $update->addCommit();
                    $result = $this->client->update($update);
                    $update = $this->client->createUpdate();
                    $this->em->clear();
                    $progressBar->advance($batch);
                }
            }
            $progressBar->finish();
            $output->writeln('');
        }
        $update->addCommit();
        $result = $this->client->update($update);
        $this->em->clear();

        return 0;
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
    public function setClient(Client $client) : void {
        $this->client = $client;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
