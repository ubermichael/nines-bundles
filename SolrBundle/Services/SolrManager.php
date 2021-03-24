<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Services;

use Knp\Component\Pager\PaginatorInterface;
use Nines\SolrBundle\Hydrator\DoctrineHydrator;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Query\QueryBuilder;
use Nines\SolrBundle\Query\Result;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;

class SolrManager
{
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

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder() {
        return new QueryBuilder($this->mapper);
    }

    /**
     * @param mixed $options
     */
    public function execute(Query $query, ?PaginatorInterface $pager = null, $options = []) : Result {
        if ($pager) {
            $paginated = $pager->paginate([$this->client, $query], $options['page'], $options['pageSize']);

            return new Result($paginated->getCustomParameter('result'), $this->hydrator, $paginated);
        }

        return new Result($this->client->select($query), $this->hydrator);
    }

    /**
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
     * @return SolrManager
     */
    public function setClient(Client $client) {
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
}
