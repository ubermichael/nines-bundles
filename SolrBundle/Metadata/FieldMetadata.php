<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionFunction;
use ReflectionMethod;

class FieldMetadata extends Metadata {
    /**
     * Name of the field as declared in the entity.
     *
     * @var string
     */
    private $fieldName;

    /**
     * Name of the field as generated in a Solr document.
     *
     * @var string
     */
    private $solrName;

    /**
     * Name of the getter function.
     *
     * @var string
     */
    private $getter;

    /**
     * Arguments to pass to the getter function.
     *
     * @var array
     */
    private $getterArgs;

    /**
     * Name of a mutator function, called on the results of the getter.
     *
     * @var string
     */
    private $mutator;

    /**
     * List of arguments to pass to the mutator function.
     *
     * @var array
     */
    private $mutatorArgs;

    /**
     * Name of functions to filter the data returned by the
     * getter and mutator. The data will be passed to the filter as the
     * first argument.
     *
     * Eg. [`strip_tags(), `html_entity_decode(ENT_QUOTES|ENT_HTML5, 'UTF-8')`]
     *
     * @var array
     */
    private $filters;

    /**
     * Arguments to pass to each filter function.
     *
     * @var array
     */
    private $filterArgs;

    public function __construct() {
        $this->getterArgs = [];
        $this->mutatorArgs = [];
        $this->filters = [];
        $this->filterArgs = [];
    }

    public function getFilters() : array {
        return $this->filters;
    }

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

    public function getGetterArgs() : array {
        return $this->getterArgs;
    }

    public function getMutatorArgs() : array {
        return $this->mutatorArgs;
    }

    public function getFilterArgs() : array {
        return $this->filterArgs;
    }

    public function addFilter(string $filter) : self {
        list($name, $args) = $this->parseFunctionCall($filter);
        $this->filters[] = $name;
        $this->filterArgs = $args;

        return $this;
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
}
