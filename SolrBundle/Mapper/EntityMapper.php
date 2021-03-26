<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\Common\Util\ClassUtils;
use Nines\SolrBundle\Logging\SolrLogger;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\UtilBundle\Entity\AbstractEntity;
use Solarium\QueryType\Update\Query\Document;

class EntityMapper
{
    /**
     * @var EntityMetadata[]
     */
    private $map;

    /**
     * Map of entity field name to solr field name.
     *
     * @var array
     */
    private $fields;

    private SolrLogger $logger;

    public function __construct() {
        $this->map = [];
        $this->fields = [
            'id' => 'id',
            'class' => 'class_s',
            'type' => 'type_s',
        ];
    }

    public function addEntity(EntityMetadata $entityMetadata) : void {
        $this->map[$entityMetadata->getClass()] = $entityMetadata;

        foreach ($entityMetadata->getFieldMetadata() as $fieldMetadata) {
            $fieldName = $fieldMetadata->getFieldName();
            $solrName = $fieldMetadata->getSolrName();
            if (array_key_exists($fieldName, $this->fields) && $this->fields[$fieldName] !== $solrName) {
                $this->logger->warning('Duplicate entity property name {property} with different solr field names {f1} and {f2}', [
                    'property' => $fieldName,
                    'f1' => $this->fields[$fieldName],
                    'f2' => $solrName,
                ]);
            }
            $this->fields[$fieldName] = $solrName;
        }
        foreach ($entityMetadata->getCopyFields() as $copyField) {
            $this->fields[$copyField['to']] = $copyField['to'];
        }
    }

    public function identify($entity) : ?string {
        if ( ! $entity) {
            return null;
        }
        $class = ClassUtils::getClass($entity);
        if ( ! ($entityMeta = ($this->map[$class] ?? null))) {
            $this->logger->warning('Cannot identify unknown class {class}.', [
                'class' => $class,
            ]);
            return null;
        }

        return $entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity);
    }

    /**
     * @param ?AbstractEntity $entity
     *
     * @return Document
     */
    public function toDocument(?AbstractEntity $entity) {
        if ( ! $entity) {
            return null;
        }
        $class = ClassUtils::getClass($entity);
        if ( ! ($entityMeta = ($this->map[$class] ?? null))) {
            $this->logger->warning('Cannot index unknown class {class}.', [
                'class' => $class,
            ]);
            return null;
        }
        $document = new Document();
        $document->setKey($entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity));
        $document->setField('id', $entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity));
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

        foreach ($entityMeta->getCopyFields() as $copyField) {
            $to = $copyField['to'];
            $v = [];

            foreach ($copyField['from'] as $from) {
                $data = $document->{$from};
                if (is_array($data)) {
                    $v = array_merge($v, $data);
                } else {
                    $v[] = $data;
                }
            }
            if (isset($document->{$to})) {
                $document->{$to} = array_merge($document->{$to}, $v);
            } else {
                $document->{$to} = $v;
            }
        }

        return $document;
    }

    public function getSolrName($name) : ?string {
        if( ! isset($this->fields[$name])) {
            $this->logger->warning('Cannot get solr name for unknown property {name}.', [
                'name' => $name,
            ]);
            return null;
        }
        return $this->fields[$name];
    }

    public function getEntityMetadata($class) : ?EntityMetadata {
        if ( ! isset($this->map[$class])) {
            $this->logger->warning('Cannot get entity metadata for unknown class {class}.', [
                'class' => $class,
            ]);
            return null;
        }

        return $this->map[$class];
    }

    public function isMapped($entity) {
        if ( ! $entity) {
            return false;
        }
        $class = ClassUtils::getClass($entity);

        return array_key_exists($class, $this->map);
    }

    public function getClasses() {
        return array_keys($this->map);
    }

    /**
     * @required
     */
    public function setSolrLogger(SolrLogger $logger) : void {
        $this->logger = $logger;
    }
}
