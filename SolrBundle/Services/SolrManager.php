<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
use ReflectionException;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Solarium\QueryType\Update\Query\Query as UpdateQuery;

/**
 * Thin wrapper around the query builder and query execution.
 */
class SolrManager {
    private ?Client $client = null;

    private ?EntityMapper $mapper = null;

    private ?DoctrineHydrator $hydrator = null;

    private ?SolrLogger $logger = null;

    private ?UpdateQuery $update = null;

    private ?bool $enabled = null;

    private ?int $pageSize = null;

    public function __construct(bool $enabled, int $pageSize) {
        $this->enabled = $enabled;
        $this->pageSize = $pageSize;
    }

    /**
     * @throws NotConfiguredException
     */
    public function createQueryBuilder() : QueryBuilder {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }

        return new QueryBuilder($this->mapper);
    }

    /**
     * Execute a query and returl the result.
     *
     * @param ?array<string,mixed> $options
     *
     * @throws NotConfiguredException
     */
    public function execute(Query $query, ?PaginatorInterface $pager = null, ?array $options = []) : ?Result {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }

        $this->logger->addQuery($query);
        if ($pager) {
            $paginated = $pager->paginate([$this->client, $query], $options['page'] ?? 1, $options['pageSize'] ?? $this->pageSize);

            return new Result($paginated->getCustomParameter('result'), $this->hydrator, $paginated);
        }

        return new Result($this->client->select($query), $this->hydrator);
    }

    /**
     * @throws NotConfiguredException
     * @throws ReflectionException
     */
    public function index(object $entity) : void {
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

    /**
     * @throws NotConfiguredException
     */
    public function flush() : void {
        if ( ! $this->client) {
            throw new NotConfiguredException();
        }
        if ( ! $this->update) {
            return;
        }
        $this->logger->addQuery($this->update);
        $this->update->addCommit();
        $result = $this->client->update($this->update);
        $this->update = null;
    }

    /**
     * @throws NotConfiguredException
     * @throws ReflectionException
     */
    public function remove(object $entity) : void {
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
     *
     * @return array<string,mixed>
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
     * @codeCoverageIgnore
     */
    public function setHydrator(DoctrineHydrator $hydrator) : self {
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
     */
    public function setClient(?Client $client) : self {
        $this->client = $client;

        return $this;
    }

    public function getMapper() : EntityMapper {
        return $this->mapper;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setMapper(EntityMapper $mapper) : self {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
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

    /**
     * @see LogLevel
     *
     * @param mixed $level
     * @param mixed $message
     * @param array<string,string> $context
     */
    public function log($level, $message, array $context = []) : void {
        $this->logger->log($level, $message, $context);
    }

    public function enabled() : bool {
        return $this->enabled;
    }
}
