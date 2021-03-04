<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Exception;
use Nines\SolrBundle\Client\Builder;
use Solarium\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PingCommand extends Command {
    private $builder;

    protected static $defaultName = 'nines:solr:ping';

    public function __construct(Builder $builder) {
        parent::__construct();
        $this->builder = $builder;
    }

    protected function configure() : void {
        $this->setDescription('Ping the solr server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $client = $this->builder->build();
        $ping = $client->createPing(['omitheader' => false]);

        try {
            $result = $client->ping($ping);
            $output->writeln('Solarium library version: ' . Client::VERSION);
            $output->writeln($result->getResponse()->getStatusCode() . ' ' . $result->getResponse()->getStatusMessage());
            $json = json_decode($result->getResponse()->getBody());
            $output->writeln('Ping: ' . $json->responseHeader->QTime . 'ms');
        } catch (Exception $e) {
            $output->writeln('Ping failed: ' . $e->getMessage());
        }

        return 0;
    }
}
