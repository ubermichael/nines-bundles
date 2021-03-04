<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
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

class ClearCommand extends Command {
    private $builder;

    protected static $defaultName = 'nines:solr:clear';

    public function __construct(Builder $builder) {
        parent::__construct();
        $this->builder = $builder;
    }

    protected function configure() : void {
        $this->setDescription('Clear the index.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $client = $this->builder->build();
        $update = $client->createUpdate();
        $update->addDeleteQuery('*:*');
        $update->addCommit();
        $result = $client->update($update);

        $output->writeln($result->getStatus() . ' documents deleted in ' . $result->getQueryTime() . 'ms');
        return 0;
    }
}
