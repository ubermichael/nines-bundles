<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
    public const ESCAPE_PHRASE = 1;

    public const ESCAPE_TERM = 2;

    public const ESCAPE_GENERAL = 3;

    public const ESCAPE_NONE = 4;

    private ?EntityMapper $mapper = null;

    /**
     * The query string for the query.
     */
    private ?string $q = null;

    /**
     * List of fields to query.
     *
     * @var array<string,float>
     */
    private array $queryFields = [];

    /**
     * Name of the default search field.
     */
    private ?string $defaultField = null;

    /**
     * Query filters as key=>value pairs.
     *
     * @var array<string,mixed>
     */
    private array $filters = [];

    /**
     * Comma separated list of field names to highlight.
     */
    private ?string $highlightFields = null;

    /**
     * Facet fields as name => solr name pairs.
     *
     * @var array<string,string>
     */
    private array $facetFields = [];

    /**
     * List of facet ranges for the query.
     *
     * @var array<string,mixed>
     */
    private array $facetRanges = [];

    /**
     * List of filter ranges for the query.
     *
     * @var array<string,mixed>
     */
    private array $filterRanges = [];

    /**
     * List of fields to return from the query.
     *
     * @var string[]
     */
    private ?array $fields = null;

    /**
     * List of field => direction pairs to sort the query results.
     *
     * @var string[]
     */
    private ?array $sorting = null;

    /**
     * Geographic filters to apply to the query.
     *
     * @var array<string,mixed>
     */
    private array $geoFilters = [];

    private ?Helper $helper = null;

    public function __construct(EntityMapper $mapper) {
        $this->q = '*:*';
        $this->mapper = $mapper;
        $this->fields = ['*', 'score'];
        $this->sorting = [
            'score' => 'desc',
        ];
        $this->helper = new Helper();
    }

    /**
     * Find the solr field name from the entity field name.
     *
     * @param array<int,string>|string $field
     *
     * @return array<int,string>|string
     */
    protected function solrName($field) {
        if (is_array($field)) {
            return array_map(fn($s) => $this->mapper->getSolrName($s) ?? $s, $field);
        }

        return $this->mapper->getSolrName($field) ?? $field;
    }

    /**
     * Set the query string.
     */
    public function setQueryString(string $q, ?int $escape = self::ESCAPE_NONE) : void {
        switch ($escape) {
            case self::ESCAPE_GENERAL:
                // see https://solr.apache.org/guide/8_8/the-standard-query-parser.html#escaping-special-characters
                $chars = quotemeta('+&|!(){}[]^"~*?:-');
                $this->q = preg_replace("/([{$chars}])/", '\\\$1', $q);

                break;

            case self::ESCAPE_PHRASE:
                $this->q = $this->helper->escapePhrase($q);

                break;

            case self::ESCAPE_TERM:
                $this->q = $this->helper->escapeTerm($q);

                break;

            case self::ESCAPE_NONE:
                $this->q = $q;

                break;
        }
    }

    /**
     * Set the default search field.
     */
    public function setDefaultField(string $defaultField) : void {
        $this->defaultField = $this->solrName($defaultField);
    }

    /**
     * @param array<int,string> $queryFields
     */
    public function setQueryFields(array $queryFields) : void {
        $this->queryFields = [];
        foreach ($queryFields as $f) {
            $this->queryFields[$this->solrName($f)] = $this->mapper->getBoost($f);
        }
    }

    public function addQueryField(string $queryField) : void {
        $this->queryFields[$this->solrName($queryField)] = $this->mapper->getBoost($queryField);
    }

    /**
     * Field or fields to highlight.
     *
     * @param array<int,string>|string $fields
     */
    public function setHighlightFields($fields) : void {
        if (is_array($fields)) {
            $this->highlightFields = implode(',', $this->solrName($fields));
        } elseif ('all' === $fields) {
            $this->highlightFields = '*';
        } else {
            $this->highlightFields = $this->mapper->getSolrName($fields);
        }
    }

    /**
     * Add a facet field.
     */
    public function addFacetField(string $name) : void {
        $this->facetFields[$name] = $this->solrName($name);
    }

    /**
     * Add a facet range to the query.
     *
     * @param mixed $start
     * @param mixed $end
     * @param mixed $gap
     */
    public function addFacetRange(string $name, $start, $end, $gap) : void {
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
     * @param array<int,mixed> $terms
     */
    public function addFilter(string $key, array $terms) : void {
        $this->filters[$this->solrName($key)] = $terms;
    }

    /**
     * Add a filter range to the query.
     *
     * @param mixed $start
     * @param mixed $end
     */
    public function addFilterRange(string $name, $start, $end) : void {
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
     */
    public function addGeographicFilter(string $name, string $latitude, string $longitude, string $distance) : void {
        $this->filters[$this->solrName($name)] = $this->helper->geofilt($this->solrName($name), $latitude, $longitude, $distance);
    }

    public function addDistanceField(string $name, string $latitude, string $longitude) : void {
        $this->fields[] = 'distance:' . $this->helper->geodist($this->solrName($name), $latitude, $longitude);
    }

    /**
     * Set the fields to return.
     *
     * @param array<int,string> $fields
     */
    public function setFields(array $fields = []) : void {
        $this->fields = array_map(fn($s) => $this->solrName($s), $fields);
    }

    /**
     * Add a field to the query output.
     */
    public function addField(string $field) : void {
        $this->fields[] = $this->solrName($field);
    }

    /**
     * Set the sorting information for the query.
     *
     * @param ?array<string,string> $sorting
     */
    public function setSorting(?array $sorting = []) : void {
        $this->sorting = [];

        foreach ($sorting as $field => $dir) {
            $this->sorting[$this->solrName($field)] = $dir;
        }
    }

    /**
     * Add a sort to the query.
     */
    public function addSorting(string $field, string $direction) : void {
        $this->sorting[$this->solrName($field)] = $direction;
    }

    public function addDistanceSorting(string $field, string $latitude, string $longitude, string $direction) : void {
        $geodist = $this->helper->geodist($this->solrName($field), $latitude, $longitude);
        $this->sorting[$geodist] = $direction;
    }

    /**
     * Generate and return a query.
     */
    public function getQuery() : Query {
        $query = new Query();
        $query->setQuery($this->q);
        if ($this->defaultField) {
            $query->setQueryDefaultField($this->defaultField);
        }
        if (count($this->queryFields) > 0) {
            $dismax = $query->getDisMax();
            $qf = array_map(fn($k, $v) => $k . ($v ? "^{$v}" : ''), array_keys($this->queryFields), $this->queryFields);
            $dismax->setQueryFields(implode(' ', $qf));
        }

        foreach ($this->filters as $key => $values) {
            $terms = $values;
            if (is_array($values)) {
                $terms = implode(' or ', array_map(fn($s) => '"' . $s . '"', $values));
            }
            $query->createFilterQuery('fq_' . $key)->addTag('exclude')
                ->setQuery("{$key}:({$terms})");
        }

        foreach ($this->filterRanges as $key => $ranges) {
            $range = implode(' OR ', array_map(fn($range) => "[{$range['start']} TO {$range['end']}]", $ranges));

            $query->createFilterQuery('fr_' . $key)->addTag('exclude')
                ->setQuery("{$key}:({$range})");
        }

        foreach ($this->geoFilters as $key => $filter) {
            $query->createFilterQuery('fq_' . $key)->addTag('exclude')
                ->setQuery();
        }

        $facetSet = $query->getFacetSet();

        foreach ($this->facetFields as $key => $value) {
            $facetSet->createFacetField($key)->setField($value)->setMinCount(1)
                ->getLocalParameters()->setExclude('exclude');
        }

        foreach ($this->facetRanges as $key => $value) {
            $facetSet->createFacetRange($key)->setField($value['field'])->setMinCount(1)
                ->setStart($value['start'])->setEnd($value['end'])->setGap($value['gap'])
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
