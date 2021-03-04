<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
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

    public function addClass($class) : void {
        $this->entityMap[$class] = [
            'class_s' => $class,
        ];
    }

    public function addId($class, $name) : void {
        $this->entityMap[$class]['id'] = $name;
    }

    public function addField($class, $name, $solr) : void {
        $this->entityMap[$class][$name] = $solr;
    }

    public function mapEntity($entity) {
        $class = get_class($entity);
        if ( ! isset($this->entityMap[$class])) {
            return;
        }
        $map = [
            'class_s' => $this->entityMap[$class]['class_s'],
        ];

        foreach ($this->entityMap[$class] as $k => $v) {
            if (in_array($v, $map, true)) {
                continue;
            }
            $getter = 'get' . lcfirst($k);
            $map[$v] = $entity->{$getter}();
        }

        return $map;
    }

    public function getCopyFields() : array {
        return $this->copyFields;
    }

    /**
     * @return EntityMapper
     */
    public function setCopyFields(array $copyFields) : self {
        $this->copyFields = $copyFields;

        return $this;
    }
}
