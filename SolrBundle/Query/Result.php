<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Knp\Component\Pager\Pagination\PaginationInterface;
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
class Result {
    private ?SolrResult $resultSet = null;

    /**
     * @var array<int,DocumentInterface>
     */
    private ?array $documents = null;

    private ?DoctrineHydrator $hydrator = null;

    private ?Highlighting $highlighting = null;

    /**
     * @var ?array<string,mixed>
     */
    private ?array $filters = null;

    private ?PaginationInterface $paginator;

    public function __construct(SolrResult $result, DoctrineHydrator $hydrator, ?PaginationInterface $paginator = null) {
        $this->resultSet = $result;
        $this->hydrator = $hydrator;
        $this->paginator = $paginator;
        $this->documents = $result->getDocuments();
    }

    /**
     * Count the results in this result set. Use total() for the number of
     * results available.
     */
    public function count() : int {
        return $this->resultSet->count();
    }

    /**
     * Get the total number of results found. Use count() for the number
     * of results in the result set.
     */
    public function total() : ?int {
        return $this->resultSet->getNumFound();
    }

    /**
     * Get the $i'th result document.
     *
     * @return DocumentInterface|stdClass
     */
    public function getDocument(int $i) {
        return $this->documents[$i];
    }

    /**
     * Get the entity corresponding to the $i'th result document.
     */
    public function getEntity(int $i) : ?object {
        return $this->hydrator->hydrate($this->documents[$i]);
    }

    /**
     * Check if result highlighting is enabled in the results.
     */
    public function hasHighlighting() : bool {
        if ($this->highlighting) {
            return true;
        }
        $this->highlighting = $this->resultSet->getHighlighting();

        return null !== $this->highlighting;
    }

    /**
     * Get the highlighted fields for the $i'th result.
     *
     * @return null|array|HighlightResult
     */
    public function getHighlighting(int $i) {
        if ( ! $this->hasHighlighting()) {
            return [];
        }
        $id = $this->getDocument($i)->id;

        return $this->highlighting->getResult($id);
    }

    /**
     * Get the named facet.
     */
    public function getFacet(string $name) : ?FacetResultInterface {
        return $this->resultSet->getFacetSet()->getFacet($name);
    }

    /**
     * Check if there are filters in this query result.
     */
    public function hasFilters() : bool {
        if ($this->filters) {
            return true;
        }

        return count($this->resultSet->getQuery()->getFilterQueries()) > 0;
    }

    /**
     * Get the filters for the query.
     *
     * @return array<int,mixed>
     */
    public function getFilters() : array {
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

    /**
     * If the query was paginated, return the paginator.
     */
    public function getPaginator() : ?PaginationInterface {
        return $this->paginator;
    }
}
