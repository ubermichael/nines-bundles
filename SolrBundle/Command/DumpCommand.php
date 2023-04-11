<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nines\SolrBundle\Mapper\EntityMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Map an entity to a Solr document and display the result.
 */
class DumpCommand extends Command {
    private ?EntityManagerInterface $em = null;

    private ?EntityMapper $mapper = null;

    protected static $defaultName = 'nines:solr:dump';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Show the solr schema.');
        $this->addArgument('class', InputArgument::REQUIRED, 'Class of the entity to dump');
        $this->addArgument('id', InputArgument::REQUIRED, 'Id of the entity to dump');
    }

    /**
     * Executes the command. Returns 0 for success.
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $class = $input->getArgument('class');
        if (false === mb_strpos($class, '\\')) {
            $class = 'App\\Entity\\' . $class;
        }
        $id = $input->getArgument('id');
        $entity = $this->em->find($class, $id);
        if ( ! $entity) {
            $output->writeln('Entity not found.');

            return 1;
        }
        $document = $this->mapper->toDocument($entity);
        if ( ! $document) {
            $output->writeln('Entity is not mapped.');

            return 2;
        }
        $table = new Table($output);
        $table->setHeaders(['Field', 'Value']);

        foreach ($document->getFields() as $f => $v) {
            $table->addRow([$f, wordwrap(is_array($v) ? implode("\n", $v) : $v, 55)]);
        }
        $table->render();

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
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
