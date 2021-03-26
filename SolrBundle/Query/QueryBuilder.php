<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Nines\SolrBundle\Mapper\EntityMapper;
use Solarium\QueryType\Select\Query\Query;

class QueryBuilder
{
    /**
     * @var EntityMapper
     */
    private $mapper;

    private $q;

    private $defaultField;

    private $filters;

    private $highlightFields;

    private $facetFields;

    private $facetRanges;

    private $filterRanges;

    private $fields;

    private $sorting;

    public function __construct(EntityMapper $mapper) {
        $this->q = '*:*';
        $this->mapper = $mapper;
        $this->filters = [];
        $this->highlightFields = [];
        $this->facetRanges = [];
        $this->facetFields = [];
        $this->filterRanges = [];
        $this->fields = ['*', 'score'];
        $this->sorting = [
            ['score', 'desc'],
        ];
    }

    protected function solrName($field) {
        if (is_array($field)) {
            return array_map(function ($s) {return $this->mapper->getSolrName($s) ?? $s; }, $field);
        }

        return $this->mapper->getSolrName($field) ?? $field;
    }

    public function setQueryString($q) : void {
        $this->q = $q;
    }

    public function setDefaultField($defaultField) : void {
        $this->defaultField = $this->solrName($defaultField);
    }

    public function setHighlightFields($fields) : void {
        if (is_array($fields)) {
            $this->highlightFields = implode(',', $this->solrName($fields));
        } elseif ('all' === $fields) {
            $this->highlightFields = 'all';
        } else {
            $this->highlightFields = $this->mapper->getSolrName($fields);
        }
    }

    public function addFacetField($name) : void {
        $this->facetFields[$name] = $this->solrName($name);
    }

    public function addFacetRange($name, $start, $end, $gap) : void {
        $this->facetRanges[$name] = [
            'field' => $this->solrName($name),
            'start' => $start,
            'end' => $end,
            'gap' => $gap,
        ];
    }

    public function addFilter($key, $terms) : void {
        $this->filters[$this->solrName($key)] = $terms;
    }

    public function addFilterRange($key, $start, $end) : void {
        if (array_key_exists($key, $this->filterRanges)) {
            $this->filterRanges[$key][] = [
                'start' => $start,
                'end' => $end,
            ];
        } else {
            $this->filterRanges[$key][0] = [
                'start' => $start,
                'end' => $end,
            ];
        }
    }

    public function setFields($fields = []) : void {
        $this->fields = [];
    }

    public function addField($field) : void {
        $this->fields[] = $field;
    }

    public function setSorting($sorting = []) : void {
        $this->sorting = $sorting;
    }

    public function addSorting($field, $direction) : void {
        $this->sorting[] = [$this->solrName($field), $direction];
    }

    /**
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

        foreach ($this->sorting as $sort) {
            $query->addSort($sort[0], $sort[1]);
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
