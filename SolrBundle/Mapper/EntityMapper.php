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
     *
     * @var
     */
    private $fieldMap;

    public function mapEntity($class, $field) {
        if ( ! isset($this->entityMap[$class])) {
            return;
        }

        return $this->entityMap[$class][$field];
    }

    public function mapField($class, $field) {
        if ( ! isset($this->fieldMap[$class])) {
            return;
        }

        return $this->fieldMap[$class][$field];
    }
}
