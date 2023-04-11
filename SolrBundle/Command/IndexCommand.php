<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Exception\SolrException;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Services\SolrManager;
use ReflectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index data in the Solr index.
 */
class IndexCommand extends Command {
    /**
     * Default batch size. Change with the -b parameter.
     */
    public const BATCH_SIZE = 250;

    private ?EntityMapper $mapper = null;

    private ?EntityManagerInterface $em = null;

    private ?SolrManager $manager = null;

    protected static $defaultName = 'nines:solr:index';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Index the data.');
        $this->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Batch size', self::BATCH_SIZE);
        $this->addArgument('classes', InputArgument::IS_ARRAY, 'Classes to index');
        $this->addOption('clear', null, InputOption::VALUE_NONE, 'Clear all entites from the index first');
    }

    /**
     * Execute the command. Returns 0 for success.
     *
     * @throws NonUniqueResultException
     * @throws NotConfiguredException
     * @throws ReflectionException
     * @throws SolrException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        try {
            $this->manager->disableLogger();
        } catch (SolrException $e) {
            $output->writeln('Index failed: ' . $e->getMessage());

            return $e->getCode();
        }

        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $batch = (int) $input->getOption('batch');
        $classes = array_map(fn($s) => false === mb_strpos($s, '\\') ? 'App\\Entity\\' . $s : $s, $input->getArgument('classes'));

        if ($input->getOption('clear')) {
            $this->manager->clear();
        }
        $n = 0;

        foreach ($this->mapper->getClasses() as $class) {
            $output->writeln($class);
            if ($classes && ! in_array($class, $classes, true)) {
                $output->writeln('skipped');

                continue;
            }

            $count = $this->em->createQuery("SELECT count(0) FROM {$class} e")->getOneOrNullResult();
            $progressBar = new ProgressBar($output, (int) $count[1]);
            $iterator = $this->em->createQuery("SELECT e FROM {$class} e ORDER BY e.id")->iterate();

            foreach ($iterator as $row) {
                $n++;
                $this->manager->index($row[0]);
                if (0 === $n % $batch) {
                    $this->manager->flush();
                    $this->em->clear();
                    $progressBar->advance($batch);
                }
            }
            $this->manager->flush();
            $this->em->clear();
            $progressBar->finish();
            $output->writeln('');
        }
        $this->manager->flush();
        $this->em->clear();

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

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
