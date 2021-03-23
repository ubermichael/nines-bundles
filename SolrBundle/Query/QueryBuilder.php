<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Nines\SolrBundle\Client\ClientBuilder;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
use Solarium\Client;

class QueryBuilder
{
    /**
     * @var EntityMapper
     */
    private $mapper;

    /**
     * @var Client
     */
    private $client;

    private $q;

    private $defaultField;

    private $filters;

    private $highlightFields;

    private $facetFields;

    private $facetRanges;

    private $filterRanges;

    public function __construct(EntityMapperBuilder $mapperBuilder, ClientBuilder $clientBuilder) {
        $this->mapper = $mapperBuilder->build();
        $this->client = $clientBuilder->build();
        $this->filters = [];
        $this->highlightFields = [];
        $this->highlightFields = [];
        $this->facetRanges = [];
        $this->filterRanges = [];
    }

    public function setQueryString($q) : void {
        $this->q = $q;
    }

    public function setDefaultField($defaultField) : void {
        $this->defaultField = $defaultField;
    }

    public function setHighlightFields($fields) : void {
        if (is_array($fields)) {
            $this->highlightFields = implode(',', $fields);
        } else {
            $this->highlightFields = $fields;
        }
    }

    public function addFacetField($name, $field) : void {
        $this->facetFields[$name] = $field;
    }

    public function addFacetRange($name, $field, $start, $end, $gap) : void {
        $this->facetRanges[$name] = [
            'field' => $field,
            'start' => $start,
            'end' => $end,
            'gap' => $gap,
        ];
    }

    public function addFilter($key, $terms) : void {
        $this->filters[$key] = $terms;
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

    public function getQuery() {
        $query = $this->client->createSelect();
        $query->setQuery($this->q);
        $query->setQueryDefaultField($this->defaultField);

        foreach ($this->filters as $key => $values) {
            $terms = join(' or ', array_map(function ($s) {return '"' . $s . '"'; }, $values));
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

        if ($this->highlightFields) {
            $highlighting = $query->getHighlighting();
            $highlighting->setFields($this->highlightFields);
            $highlighting->setSimplePrefix("<span class='hl'>");
            $highlighting->setSimplePostfix('</span>');
        }

        return $query;
    }

    public function getClient() {
        return $this->client;
    }
}
