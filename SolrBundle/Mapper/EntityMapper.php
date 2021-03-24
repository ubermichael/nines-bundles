<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\Common\Util\ClassUtils;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\UtilBundle\Entity\AbstractEntity;
use Solarium\QueryType\Update\Query\Document;

class EntityMapper
{
    /**
     * @var EntityMetadata[]
     */
    private $map;

    public function __construct() {
        $this->map = [];
    }

    public function addEntity(EntityMetadata $entityMetadata) : void {
        $this->map[$entityMetadata->getClass()] = $entityMetadata;
    }

    /**
     * @return Document
     */
    public function toDocument(?AbstractEntity $entity) {
        if ( ! $entity) {
            return null;
        }
        $class = ClassUtils::getClass($entity);
        if ( ! ($entityMeta = ($this->map[$class] ?? null))) {
            return null;
        }
        $document = new Document();
        $document->setKey($entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity));
        $document->setField('class_s', $entityMeta->getClass());

        foreach ($entityMeta->getFixed() as $key => $value) {
            $document->setField($key, $value);
        }

        foreach ($entityMeta->getFieldMetadata() as $fieldMetadata) {
            $document->setField($fieldMetadata->getSolrName(), $fieldMetadata->fetch($entity));
            $boost = $fieldMetadata->getBoost();
            if ($boost && 1.0 !== $boost) {
                $document->setFieldBoost($fieldMetadata->getSolrName(), $boost);
            }
        }

        return $document;
    }

    public function getSolrName($class, $name) {
        if ( ! isset($this->map[$class])) {
            return;
        }
        $entityMeta = $this->map[$class];
        $fieldMeta = $entityMeta->getFieldMetadata();
        if ( ! isset($fieldMeta[$name])) {
            return;
        }

        return $fieldMeta[$name]->getSolrName();
    }

    public function getEntityMetadata($class) {
        if ( ! isset($this->map[$class])) {
            return;
        }

        return $this->map[$class];
    }

    public function getClasses() {
        return array_keys($this->map);
    }
}
