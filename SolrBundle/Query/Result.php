<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Nines\SolrBundle\Hydrator\DoctrineHydrator;
use Solarium\Component\Result\Highlighting\Highlighting;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Result\Result as SolrResult;

class Result
{
    /**
     * @var SolrResult
     */
    private $resultSet;

    /**
     * @var DocumentInterface[]
     */
    private $documents;

    /**
     * @var array
     */
    private $entities;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    /**
     * @var ?Highlighting
     */
    private $highlighting;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(SolrResult $result, DoctrineHydrator $hydrator, ?PaginationInterface $paginator = null) {
        $this->resultSet = $result;
        $this->hydrator = $hydrator;
        $this->paginator = $paginator;

        $this->documents = $result->getDocuments();
        $this->entities = [];
        $this->highlighting = null;
        $this->filters = null;
    }

    public function count() {
        return $this->resultSet->count();
    }

    public function total() {
        return $this->resultSet->getNumFound();
    }

    public function getDocument($i) {
        return $this->documents[$i];
    }

    public function getEntity($i) {
        return $this->hydrator->hydrate($this->documents[$i]);
    }

    public function hasHighlighting() {
        if ($this->highlighting) {
            return true;
        }
        $this->highlighting = $this->resultSet->getHighlighting();

        return null !== $this->highlighting;
    }

    public function getHighlighting($i) {
        if ( ! $this->hasHighlighting()) {
            return [];
        }
        $id = $this->getDocument($i)->id;

        return $this->highlighting->getResult($id);
    }

    public function getFacet($name) {
        return $this->resultSet->getFacetSet()->getFacet($name);
    }

    public function hasFilters() {
        if ($this->filters) {
            return true;
        }

        return count($this->resultSet->getQuery()->getFilterQueries()) > 0;
    }

    /**
     * @return ?PaginatorInterface
     */
    public function getPaginator() {
        return $this->paginator;
    }

    public function getFilters() {
        if (null === $this->filters) {
            $this->filters = [];

            foreach ($this->resultSet->getQuery()->getFilterQueries() as $fq) {
                list($field, $query) = explode(':', $fq->getQuery());
                $label = implode(' ', array_slice(explode('_', $field), 0, -1));
                $this->filters[] = [
                    'field' => $field,
                    'label' => $label,
                    'query' => $query,
                ];
            }
        }

        return $this->filters;
    }
}
