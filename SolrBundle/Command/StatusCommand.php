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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Check the status of the Solr server.
 */
class StatusCommand extends Command
{
    private Client $client;

    /**
     * Name of the core for this application.
     *
     * @var string
     */
    private $core;

    protected static $defaultName = 'nines:solr:status';

    /**
     * Construct the command.
     */
    public function __construct(ParameterBagInterface $parameters, ?string $name = null) {
        parent::__construct($name);
        $this->core = $parameters->get('nines.solr.core');
    }

    /**
     * Configure the command.
     */
    protected function configure() : void {
        $this->setDescription('Check the status of a Solr core.');
    }

    /**
     * Execute the command. Returns 0 for success.
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        // create a core admin query
        $coreAdminQuery = $this->client->createCoreAdmin();

        // use the core admin query to build a Status action
        $statusAction = $coreAdminQuery->createStatus();
        $statusAction->setCore($this->core);
        $coreAdminQuery->setAction($statusAction);

        try {
            $response = $this->client->coreAdmin($coreAdminQuery);
            $statusResult = $response->getStatusResult();

            $output->writeln($response->getResponse()->getStatusCode() . ' ' . $response->getResponse()->getStatusMessage());
            $output->writeln('Core: ' . $statusResult->getCoreName());
            $output->writeln('Version: ' . $statusResult->getVersion());
            $output->writeln('Documents: ' . $statusResult->getNumberOfDocuments());
            $seconds = round($statusResult->getUptime() / 1000);
            $output->writeln('Uptime: ' . sprintf('%d days, %02d:%02d:%02d', $seconds / 86400, $seconds / 3600, ($seconds / 60) % 60, $seconds % 60));
            $output->writeln('Started: ' . $statusResult->getStartTime()->format('Y-m-d H:i:s'));
            $output->writeln('Updated: ' . $statusResult->getLastModified()->format('Y-m-d H:i:s'));
        } catch (Exception $e) {
            $output->writeln('Ping failed: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * @required
     */
    public function setClient(Client $client) : void {
        $this->client = $client;
    }
}
