<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Metadata about a field in an entity.
 */
class FieldMetadata extends Metadata {
    /**
     * Name of the field as declared in the entity.
     */
    private ?string $fieldName = null;

    /**
     * Name of the field as generated in a Solr document.
     */
    private ?string $solrName = null;

    private ?float $boost = null;

    /**
     * Name of the getter function.
     */
    private ?string $getter = null;

    /**
     * Arguments to pass to the getter function.
     *
     * @var array<int,string>
     */
    private array $getterArgs = [];

    /**
     * Name of a mutator function, called on the results of the getter.
     */
    private ?string $mutator = null;

    /**
     * List of arguments to pass to the mutator function.
     *
     * @var array<int,string>
     */
    private ?array $mutatorArgs = null;

    /**
     * Name of functions to filter the data returned by the
     * getter and mutator. The data will be passed to the filter as the
     * first argument.
     *
     * Eg. [`strip_tags(), `html_entity_decode(ENT_QUOTES|ENT_HTML5, 'UTF-8')`]
     *
     * @var array<int,string>
     */
    private array $filters = [];

    /**
     * Arguments to pass to each filter function.
     *
     * @var array<int,array<int,string>>
     */
    private array $filterArgs = [];

    /**
     * FieldMetadata constructor.
     */
    public function __construct() {
        $this->getterArgs = [];
        $this->mutatorArgs = [];
        $this->filters = [];
        $this->filterArgs = [];
    }

    /**
     * Get a list of filters for data processing.
     *
     * @return array<int,string>
     */
    public function getFilters() : array {
        return $this->filters;
    }

    /**
     * Add filters to the metadata.
     *
     * @param ?array<int,string> $filters
     */
    public function setFilters(?array $filters) : self {
        if ( ! $filters) {
            $this->filters = [];
            $this->filterArgs = [];
        } else {
            foreach ($filters as $filter) {
                list($name, $args) = $this->parseFunctionCall($filter);
                $this->filters[] = $name;
                $this->filterArgs[] = $args;
            }
        }

        return $this;
    }

    /**
     * Get a list of filter function arguments. Each array item is a list of
     * arguments for a filter function.
     *
     * @return array<int,array<int,string>>
     */
    public function getFilterArgs() : array {
        return $this->filterArgs;
    }

    /**
     * Add a filter for the field. The field data will be passed as the first
     * argument to the filter function.
     */
    public function addFilter(string $filter) : self {
        list($name, $args) = $this->parseFunctionCall($filter);
        $this->filters[] = $name;
        $this->filterArgs = $args;

        return $this;
    }

    /**
     * Get a list of arguments to pass to the getter function for this field.
     *
     * @return array<int,string>
     */
    public function getGetterArgs() : array {
        return $this->getterArgs;
    }

    /**
     * Get a list of arguments to pass to the mutator function for this field.
     *
     * @return array<int,string>
     */
    public function getMutatorArgs() : array {
        return $this->mutatorArgs;
    }

    public function getFieldName() : string {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName) : self {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getSolrName() : string {
        return $this->solrName;
    }

    public function setSolrName(string $solrName) : self {
        $this->solrName = $solrName;

        return $this;
    }

    public function getGetter() : string {
        return $this->getter;
    }

    public function setGetter(string $getter) : self {
        list($name, $args) = $this->parseFunctionCall($getter);
        $this->getter = $name;
        $this->getterArgs = $args;

        return $this;
    }

    public function getMutator() : ?string {
        return $this->mutator;
    }

    public function setMutator(?string $mutator) : self {
        if ( ! $mutator) {
            $this->mutator = null;
            $this->mutatorArgs = [];
        } else {
            list($name, $args) = $this->parseFunctionCall($mutator);
            $this->mutator = $name;
            $this->mutatorArgs = $args;
        }

        return $this;
    }

    /**
     * Fetch a representation of the field value which is suitable for indexing.
     *
     * The process is
     *   1. data is first fetched via the getter.
     *   2. If $mutator is defined it is called as a method on the data.
     *   3. If $filters are defined, they are called as functions with the data
     *      as the first argument.
     *   4. The data is returned.
     *
     * @throws ReflectionException
     *
     * @return null|array<int,mixed>|string
     */
    public function fetch(AbstractEntity $entity) {
        $data = null;
        if ($this->getterArgs) {
            $ref = new ReflectionMethod($entity, $this->getter);
            $data = $ref->invokeArgs($entity, $this->getterArgs);
        } else {
            $method = $this->getter;
            $data = $entity->{$method}();
        }

        if ( ! $data) {
            return $data;
        }

        if ($this->mutator) {
            if ($this->mutatorArgs) {
                $ref = new ReflectionMethod($data, $this->mutator);
                $data = $ref->invokeArgs($data, $this->mutatorArgs);
            } else {
                $method = $this->mutator;
                $data = $data->{$method}();
            }
        }

        foreach ($this->filters as $n => $filter) {
            $args = $this->filterArgs[$n];
            if ($args) {
                $ref = new ReflectionFunction($filter);
                $args = array_merge([$data], $args);
                $data = $ref->invokeArgs($args);
            } else {
                $data = $filter($data);
            }
        }

        return $data;
    }

    public function getBoost() : ?float {
        return $this->boost;
    }

    public function setBoost(?float $boost) : void {
        $this->boost = $boost;
    }
}
