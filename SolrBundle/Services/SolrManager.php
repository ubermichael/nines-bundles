<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Services;

use Knp\Component\Pager\PaginatorInterface;
use Nines\SolrBundle\Client\LoggerPlugin;
use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Exception\SolrException;
use Nines\SolrBundle\Hydrator\DoctrineHydrator;
use Nines\SolrBundle\Logging\SolrLogger;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Query\QueryBuilder;
use Nines\SolrBundle\Query\Result;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

/**
 * Thin wrapper around the query builder and query execution.
 */
class SolrManager {
    /**
     * @var Client
     */
    private $client;

    /**
     * @var EntityMapper
     */
    private $mapper;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    private SolrLogger $logger;

    /**
     * @var UpdateQuery
     */
    private $update;

    /**
     * @var bool
     */
    private $enabled;

    public function __construct($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }

        return new QueryBuilder($this->mapper);
    }

    /**
     * Execute a query and returl the result.
     *
     * @param mixed $options
     * @param ?PaginatorInterface $pager
     */
    public function execute(Query $query, ?PaginatorInterface $pager = null, $options = []) : ?Result {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        if ( ! $this->client) {
            $this->logger->error('No client configured for this envirnoment.');

            return null;
        }

        $this->logger->addQuery($query);
        if ($pager) {
            $paginated = $pager->paginate([$this->client, $query], $options['page'], $options['pageSize']);

            return new Result($paginated->getCustomParameter('result'), $this->hydrator, $paginated);
        }

        return new Result($this->client->select($query), $this->hydrator);
    }

    public function index($entity) : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        if ( ! $this->mapper->isMapped($entity)) {
            return;
        }
        if ( ! $this->update) {
            $this->update = $this->client->createUpdate();
        }
        $this->update->addDocument($this->mapper->toDocument($entity));
    }

    public function flush() : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        if ( ! $this->update) {
            return;
        }
        $this->logger->addQuery($this->update);
        $this->update->addCommit();
        $this->client->update($this->update);
        $this->update = null;
    }

    public function remove($entity) : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        if ( ! $this->mapper->isMapped($entity)) {
            return;
        }
        if ( ! $this->update) {
            $this->update = $this->client->createUpdate();
        }
        $this->update->addDeleteById($this->mapper->identify($entity));
    }

    /**
     * @throws SolrException
     */
    public function clear() : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        $query = $this->client->createUpdate();
        $query->addDeleteQuery('*:*');
        $query->addCommit();
        $this->logger->addQuery($query);
        $this->client->update($query);
    }

    /**
     * @throws SolrException
     */
    public function ping() : array {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        $ping = $this->client->createPing(['omitheader' => false]);

        $result = $this->client->ping($ping);
        $json = json_decode($result->getResponse()->getBody());

        return [
            'solarium_version' => Client::VERSION,
            'status_code' => $result->getResponse()->getStatusCode(),
            'response_message' => $result->getResponse()->getStatusMessage(),
            'request_time' => $json->responseHeader->QTime,
        ];
    }

    /**
     * Get the hydrator.
     *
     * @return mixed
     */
    public function getHydrator() {
        return $this->hydrator;
    }

    /**
     * @required
     *
     * @return SolrManager
     */
    public function setHydrator(DoctrineHydrator $hydrator) {
        $this->hydrator = $hydrator;

        return $this;
    }

    public function getClient() : Client {
        return $this->client;
    }

    /**
     * @required
     *
     * @param ?Client $client
     *
     * @return SolrManager
     */
    public function setClient(?Client $client) {
        $this->client = $client;

        return $this;
    }

    public function getMapper() : EntityMapper {
        return $this->mapper;
    }

    /**
     * @required
     *
     * @return SolrManager
     */
    public function setMapper(EntityMapper $mapper) {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @required
     */
    public function setSolrLogger(SolrLogger $logger) : void {
        $this->logger = $logger;
    }

    /**
     * Large indexing operations may cause the logger to accumulate large
     * buffers which aren't used for anything.
     *
     * @throws SolrException
     */
    public function disableLogger() : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        $this->client->getPlugin(LoggerPlugin::class)->setOptions(['enabled' => false]);
    }

    public function log($level, $message, $context = []) : void {
        $this->logger->log($level, $message, $context);
    }

    public function enabled() : bool {
        return $this->enabled;
    }
}
