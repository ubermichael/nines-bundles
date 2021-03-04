<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

class EntityMapper {
    /**
     * Maps doctrine entity to solr fields.
     *
     * @var array
     */
    private $entityMap;

    /**
     * Maps solr fields to doctrine entity.
     */
    private $fieldMap;

    /**
     * @var array
     */
    private $copyFields;

    public function __construct() {
        $this->copyFields = [];
    }

    public function addClass($class) {
        $this->entityMap[$class] = [
            'class_s' => $class,
        ];
    }

    public function addId($class, $name) {
        $this->entityMap[$class]['id'] = $name;
    }

    public function addField($class, $name, $solr) {
        $this->entityMap[$class][$name] = $solr;
    }

    public function mapEntity($entity) {
        $class = get_class($entity);
        if( ! isset($this->entityMap[$class])) {
            return;
        }
        $map = [
            'class_s' => $this->entityMap[$class]['class_s'],
        ];
        foreach($this->entityMap[$class] as $k => $v) {
            if(in_array($v, $map)) {
                continue;
            }
            $getter = "get" . lcfirst($k);
            $map[$v] = $entity->$getter();
        }
        return $map;
    }

    /**
     * @return array
     */
    public function getCopyFields() : array {
        return $this->copyFields;
    }

    /**
     * @param array $copyFields
     *
     * @return EntityMapper
     */
    public function setCopyFields(array $copyFields) : self {
        $this->copyFields = $copyFields;
        return $this;
    }
}
