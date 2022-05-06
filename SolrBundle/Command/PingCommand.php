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
 * Ping the server.
 */
class PingCommand extends Command {
    private ?SolrManager $manager = null;

    protected static $defaultName = 'nines:solr:ping';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Ping the solr server.');
    }

    /**
     * Execute the command. Returns 0 for success.
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int {
        try {
            $ping = $this->manager->ping();
            $output->writeln('Solarium library version: ' . $ping['solarium_version']);
            $output->writeln($ping['status_code'] . ' ' . $ping['response_message']);
            $output->writeln('Ping: ' . $ping['request_time'] . 'ms');
        } catch (Exception $e) {
            $output->writeln('Ping failed: ' . $e->getMessage());

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
