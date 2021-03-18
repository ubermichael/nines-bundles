<?php


namespace Nines\SolrBundle\Query;


use Nines\SolrBundle\Client\ClientBuilder;
use Nines\SolrBundle\Mapper\EntityMapper;
use Nines\SolrBundle\Mapper\EntityMapperBuilder;
use Solarium\Client;

class QueryBuilder {

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

    public function __construct(EntityMapperBuilder $mapperBuilder, ClientBuilder $clientBuilder) {
        $this->mapper = $mapperBuilder->build();
        $this->client = $clientBuilder->build();
        $this->filters = [];
        $this->highlightFields = [];
        $this->highlightFields = [];
    }

    public function setQueryString($q) {
        $this->q = $q;
    }

    public function setDefaultField($defaultField) {
        $this->defaultField = $defaultField;
    }

    public function setHighlightFields($fields) {
        if(is_array($fields)) {
            $this->highlightFields = implode(',', $fields);
        } else {
            $this->highlightFields = $fields;
        }
    }

    public function addFacetField($name, $field) {
        $this->facetFields[$name] = $field;
    }

    public function addFilter($key, $terms) {
        $this->filters[$key] = $terms;
    }

    public function getQuery() {
        $query = $this->client->createSelect();
        $query->setQuery($this->q);
        $query->setQueryDefaultField($this->defaultField);
        foreach($this->filters as $key => $values) {
            $terms = join(' or ', $values);
            $query->createFilterQuery('fq_' . $key)->addTag('exclude')->setQuery("{$key}:({$terms})");
        }
        if($this->facetFields) {
            $facetSet = $query->getFacetSet();
            foreach ($this->facetFields as $key => $value) {
                $facetSet->createFacetField($key)->setField($value)->getLocalParameters()->setExclude('exclude');
            }
        }
        if($this->highlightFields) {
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
