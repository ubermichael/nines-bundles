<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Client;

use Nines\SolrBundle\Logging\SolrLogger;
use Solarium\Core\Event\Events;
use Solarium\Core\Event\PostExecuteRequest;
use Solarium\Core\Plugin\AbstractPlugin;

/**
 * Logger plugin collects queries and query metadata for debugging purposes.
 */
class LoggerPlugin extends AbstractPlugin
{
    /**
     * @var SolrLogger
     */
    private $logger;

    /**
     * Pass ['enabled' => false] to disable the plugin.
     *
     * @param null|array $options
     */
    public function __construct($options = null) {
        if (null === $options) {
            $options = ['enabled' => true];
        } else {
            if (is_array($options)) {
                $options['enabled'] = true;
            }
        }
        parent::__construct($options);
    }

    /**
     * Enable or disable the plugin.
     *
     * @param $enabled
     */
    public function setEnabled($enabled) : void {
        $this->setOption('enabled', $enabled);
        $this->logger->setEnabled($enabled);
    }

    /**
     * Sets up an event listener for the Solr client.
     */
    public function initPluginType() : void {
        $dispatcher = $this->client->getEventDispatcher();
        $dispatcher->addListener(Events::POST_EXECUTE_REQUEST, [$this, 'postExecuteRequest']);
    }

    /**
     * Callback function called after a query is executed. Collects metadata
     * about the query.
     *
     * @param PostExecuteRequest $event
     */
    public function postExecuteRequest(PostExecuteRequest $event) : void {
        if ( ! $this->getOption('enabled')) {
            return;
        }
        $this->logger->notice('Query to {server} {request}', [
            'server' => $event->getEndpoint()->getServerUri(),
            'request' => urldecode($event->getRequest()->getUri()),
        ]);
    }

    /**
     * @required
     */
    public function setSolrLogger(SolrLogger $logger) : void {
        $this->logger = $logger;
    }
}
