<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Exception;
use Nines\SolrBundle\Services\SolrManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Delete all content from the index.
 */
class ClearCommand extends Command {
    private ?SolrManager $manager = null;

    protected static $defaultName = 'nines:solr:clear';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Clear the index.');
    }

    /**
     * Execute the command. Returns 0 for success.
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        try {
            $this->manager->clear();
        } catch (Exception $e) {
            $output->writeln('Clear failed: ' . $e->getMessage());

            return $e->getCode();
        }

        return 0;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }
}
