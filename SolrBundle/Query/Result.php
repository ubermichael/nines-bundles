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
use Solarium\Component\Result\Facet\FacetResultInterface;
use Solarium\Component\Result\Highlighting\Highlighting;
use Solarium\Component\Result\Highlighting\Result as HighlightResult;
use Solarium\Core\Query\DocumentInterface;
use Solarium\QueryType\Select\Result\Result as SolrResult;
use stdClass;

/**
 * Query result set.
 */
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
     * @var ?PaginatorInterface
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

    /**
     * Count the results in this result set. Use total() for the number of
     * results available.
     *
     * @return int
     */
    public function count() {
        return $this->resultSet->count();
    }

    /**
     * Get the total number of results found. Use count() for the number
     * of results in the result set.
     * @return int|null
     */
    public function total() {
        return $this->resultSet->getNumFound();
    }

    /**
     * Get the $i'th result document.
     *
     * @param int $i
     *
     * @return stdClass|DocumentInterface
     */
    public function getDocument($i) {
        return $this->documents[$i];
    }

    /**
     * Get the entity corresponding to the $i'th result document.
     *
     * @param int $i
     *
     * @return object|null
     */
    public function getEntity($i) {
        return $this->hydrator->hydrate($this->documents[$i]);
    }

    /**
     * Check if result highlighting is enabled in the results.
     *
     * @return bool
     */
    public function hasHighlighting() {
        if ($this->highlighting) {
            return true;
        }
        $this->highlighting = $this->resultSet->getHighlighting();

        return null !== $this->highlighting;
    }

    /**
     * Get the highlighted fields for the $i'th result.
     *
     * @param int $i
     *
     * @return array|HighlightResult|null
     */
    public function getHighlighting($i) {
        if ( ! $this->hasHighlighting()) {
            return [];
        }
        $id = $this->getDocument($i)->id;

        return $this->highlighting->getResult($id);
    }

    /**
     * Get the named facet.
     *
     * @param $name
     *
     * @return FacetResultInterface|null
     */
    public function getFacet($name) {
        return $this->resultSet->getFacetSet()->getFacet($name);
    }

    /**
     * Check if there are filters in this query result.
     *
     * @return bool
     */
    public function hasFilters() {
        if ($this->filters) {
            return true;
        }

        return count($this->resultSet->getQuery()->getFilterQueries()) > 0;
    }

    /**
     * Get the filters for the query.
     * @return array
     */
    public function getFilters() {
        if (null === $this->filters) {
            $this->filters = [];

            foreach ($this->resultSet->getQuery()->getFilterQueries() as $fq) {
                [$field, $query] = explode(':', $fq->getQuery());
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

    /**
     * If the query was paginated, return the paginator.
     *
     * @return ?PaginatorInterface
     */
    public function getPaginator() {
        return $this->paginator;
    }

}
