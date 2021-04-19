<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Exception;
use Solarium\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Ping the server.
 */
class PingCommand extends Command {
    private Client $client;

    protected static $defaultName = 'nines:solr:ping';

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Ping the solr server.');
    }

    /**
     * Execute the command. Returns 0 for success.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if( ! $this->client) {
            $output->writeln("No configured Solr client.");
            return 1;
        }
        $ping = $this->client->createPing(['omitheader' => false]);

        try {
            $result = $this->client->ping($ping);
            $output->writeln('Solarium library version: ' . Client::VERSION);
            $output->writeln($result->getResponse()->getStatusCode() . ' ' . $result->getResponse()->getStatusMessage());
            $json = json_decode($result->getResponse()->getBody());
            $output->writeln('Ping: ' . $json->responseHeader->QTime . 'ms');
        } catch (Exception $e) {
            $output->writeln('Ping failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * @required
     */
    public function setClient(?Client $client) : void {
        $this->client = $client;
    }
}
