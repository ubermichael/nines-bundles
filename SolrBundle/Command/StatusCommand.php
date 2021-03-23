<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Command;

use Exception;
use Nines\SolrBundle\Client\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatusCommand extends Command
{
    private $builder;

    protected static $defaultName = 'nines:solr:status';

    public function __construct(ClientBuilder $builder) {
        parent::__construct();
        $this->builder = $builder;
    }

    protected function configure() : void {
        $this->setDescription('Check the status of a Solr core.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $client = $this->builder->build();
        // create a core admin query
        $coreAdminQuery = $client->createCoreAdmin();

        // use the core admin query to build a Status action
        $statusAction = $coreAdminQuery->createStatus();
        $statusAction->setCore('doceww');
        $coreAdminQuery->setAction($statusAction);

        try {
            $response = $client->coreAdmin($coreAdminQuery);
            $statusResult = $response->getStatusResult();

            $output->writeln($response->getResponse()->getStatusCode() . ' ' . $response->getResponse()->getStatusMessage());
            $output->writeln('Core: ' . $statusResult->getCoreName());
            $output->writeln('Version: ' . $statusResult->getVersion());
            $seconds = round($statusResult->getUptime() / 1000);
            $output->writeln('Uptime: ' . sprintf('%d days, %02d:%02d:%02d', $seconds / 86400, $seconds / 3600, ($seconds / 60) % 60, $seconds % 60));
            $output->writeln('Documents: ' . $statusResult->getNumberOfDocuments());
            $output->writeln('Created: ' . $statusResult->getStartTime()->format('Y-m-d H:i:s'));
            $output->writeln('Updated: ' . $statusResult->getLastModified()->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            $output->writeln('Ping failed: ' . $e->getMessage());
        }

        return 0;
    }
}
