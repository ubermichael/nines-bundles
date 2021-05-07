<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Nines\SolrBundle\Mapper\EntityMapper;
use Solarium\Core\Query\Helper;
use Solarium\QueryType\Select\Query\Query;

/**
 * Helper class to build a solr query.
 *
 * @todo add a near() function or something like it for geofilt and geodist.
 */
class QueryBuilder {
    /**
     * @var EntityMapper
     */
    private $mapper;

    /**
     * The query string for the query.
     *
     * @var string
     */
    private $q;

    /**
     * Name of the default search field.
     *
     * @var string
     */
    private $defaultField;

    /**
     * Query filters as key=>value pairs.
     *
     * @var array
     */
    private $filters;

    /**
     * Names of fields to highlight.
     *
     * @var array
     */
    private $highlightFields;

    /**
     * Facet fields as name => solr name pairs.
     *
     * @var array
     */
    private $facetFields;

    /**
     * List of facet ranges for the query.
     *
     * @var array
     */
    private $facetRanges;

    /**
     * List of filter ranges for the query.
     *
     * @var array
     */
    private $filterRanges;

    /**
     * List of fields to return from the query.
     *
     * @var string[]
     */
    private $fields;

    /**
     * List of field => direction pairs to sort the query results.
     *
     * @var string[]
     */
    private $sorting;

    /**
     * Geographic filters to apply to the query.
     */
    private array $geoFilters;

    private Helper $helper;

    public function __construct(EntityMapper $mapper) {
        $this->q = '*:*';
        $this->mapper = $mapper;
        $this->filters = [];
        $this->filterRanges = [];
        $this->geoFilters = [];
        $this->highlightFields = [];
        $this->facetRanges = [];
        $this->facetFields = [];
        $this->fields = ['*', 'score'];
        $this->sorting = [
            ['score', 'desc'],
        ];
        $this->helper = new Helper();
    }

    /**
     * Find the solr field name from the entity field name.
     *
     * @param $field
     *
     * @return array|string|string[]
     */
    protected function solrName($field) {
        if (is_array($field)) {
            return array_map(function ($s) {return $this->mapper->getSolrName($s) ?? $s; }, $field);
        }

        return $this->mapper->getSolrName($field) ?? $field;
    }

    /**
     * Set the query string.
     *
     * @param $q
     */
    public function setQueryString($q) : void {
        $this->q = $q;
    }

    /**
     * Set the default search field.
     *
     * @param $defaultField
     */
    public function setDefaultField($defaultField) : void {
        $this->defaultField = $this->solrName($defaultField);
    }

    /**
     * Field or fields to highlight.
     *
     * @param mixed $fields
     */
    public function setHighlightFields($fields) : void {
        if (is_array($fields)) {
            $this->highlightFields = implode(',', $this->solrName($fields));
        } elseif ('all' === $fields) {
            $this->highlightFields = 'all';
        } else {
            $this->highlightFields = $this->mapper->getSolrName($fields);
        }
    }

    /**
     * Add a facet field.
     *
     * @param $name
     */
    public function addFacetField($name) : void {
        $this->facetFields[$name] = $this->solrName($name);
    }

    /**
     * Add a facet range to the query.
     *
     * @param string $name
     * @param numeric $start
     * @param numeric $end
     * @param numeric $gap
     */
    public function addFacetRange($name, $start, $end, $gap) : void {
        $this->facetRanges[$name] = [
            'field' => $this->solrName($name),
            'start' => $start,
            'end' => $end,
            'gap' => $gap,
        ];
    }

    /**
     * Add a filter to the search query.
     *
     * @param string $key
     * @param array $terms
     */
    public function addFilter($key, $terms) : void {
        $this->filters[$this->solrName($key)] = $terms;
    }

    /**
     * Add a filter range to the query.
     *
     * @param string $name
     * @param numeric $start
     * @param numeric $end
     */
    public function addFilterRange($name, $start, $end) : void {
        $solrName = $this->solrName($name);
        if (array_key_exists($solrName, $this->filterRanges)) {
            $this->filterRanges[$this->solrName($name)][] = [
                'start' => $start,
                'end' => $end,
            ];
        } else {
            $this->filterRanges[$this->solrName($solrName)][0] = [
                'start' => $start,
                'end' => $end,
            ];
        }
    }

    /**
     * Filter search results by geographic distance from field $name.
     *
     * @param string $name
     * @param string $latitude
     * @param string $longitude
     * @param string $distance
     */
    public function addGeographicFilter($name, $latitude, $longitude, $distance) : void {
        $this->filters[$this->solrName($name)] = $this->helper->geofilt($this->solrName($name), $latitude, $longitude, $distance);
    }

    public function addDistanceField($name, $latitude, $longitude) : void {
        $this->fields[] = 'distance:' . $this->helper->geodist($this->solrName($name), $latitude, $longitude);
    }

    /**
     * Set the fields to return.
     *
     * @param array $fields
     */
    public function setFields($fields = []) : void {
        $this->fields = array_map(function ($s) {return $this->solrName($s); }, $fields);
    }

    /**
     * Add a field to the query output.
     *
     * @param $field
     */
    public function addField($field) : void {
        $this->fields[] = $this->solrName($field);
    }

    /**
     * Set the sorting information for the query.
     *
     * @param array $sorting
     */
    public function setSorting($sorting = []) : void {
        $this->sorting = [];

        foreach ($sorting as $field => $dir) {
            $this->sorting[$this->solrName($field)] = $dir;
        }
    }

    /**
     * Add a sort to the query.
     *
     * @param $field
     * @param $direction
     */
    public function addSorting($field, $direction) : void {
        $this->sorting[] = [$this->solrName($field), $direction];
    }

    public function addDistanceSorting($field, $latitude, $longitude, $direction) : void {
        $geodist = $this->helper->geodist($this->solrName($field), $latitude, $longitude);
        $this->sorting[] = [$geodist, $direction];
    }

    /**
     * Generate and return a query.
     *
     * @return Query
     */
    public function getQuery() {
        $query = new Query();
        $query->setQuery($this->q);
        if ($this->defaultField) {
            $query->setQueryDefaultField($this->defaultField);
        }

        foreach ($this->filters as $key => $values) {
            $terms = $values;
            if (is_array($values)) {
                $terms = join(' or ', array_map(function ($s) { return '"' . $s . '"'; }, $values));
            }
            $query->createFilterQuery('fq_' . $key)->addTag('exclude')
                ->setQuery("{$key}:({$terms})")
            ;
        }

        foreach ($this->filterRanges as $key => $ranges) {
            $range = implode(' OR ', array_map(function ($range) {
                return "[{$range['start']} TO {$range['end']}]";
            }, $ranges));

            $query->createFilterQuery('fr_' . $key)->addTag('exclude')
                ->setQuery("{$key}:({$range})")
            ;
        }

        foreach ($this->geoFilters as $key => $filter) {
            $query->createFilterQuery('fq_' . $key)->addTag('exclude')
                ->setQuery()
            ;
        }

        $facetSet = $query->getFacetSet();

        foreach ($this->facetFields as $key => $value) {
            $facetSet->createFacetField($key)->setField($value)->setMinCount(1)
                ->getLocalParameters()->setExclude('exclude');
        }

        foreach ($this->facetRanges as $key => $value) {
            $facetSet->createFacetRange($key)->setField($value['field'])->setMinCount(1)
                ->setStart($value['start'])->setEnd($value['end'])->setGap(50)
                ->getLocalParameters()->setExclude('exclude');
        }

        foreach ($this->fields as $field) {
            $query->addField($field);
        }

        foreach ($this->sorting as $k => $v) {
            $query->addSort($k, $v);
        }

        if ($this->highlightFields) {
            $highlighting = $query->getHighlighting();
            $highlighting->setFields($this->highlightFields);
            $highlighting->setSimplePrefix("<span class='hl'>");
            $highlighting->setSimplePostfix('</span>');
        }

        return $query;
    }
}
