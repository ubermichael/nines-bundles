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
use Solarium\QueryType\Update\Query\Document;

/**
 * Maps entities and their fields to Solr documents and field names.
 */
class EntityMapper {
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

    /**
     * Map of entity field names to boost values.
     *
     * @var array
     */
    private $boosts;

    private SolrLogger $logger;

    /**
     * EntityMapper constructor.
     *
     * @see EntityMapperFactory
     */
    public function __construct() {
        $this->map = [];
        $this->boosts = [];
        $this->fields = [
            'id' => 'id',
            'class' => 'class_s',
            'type' => 'type_s',
        ];
    }

    /**
     * Add the metadata for one entity to the mapper.
     */
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
            $this->boosts[$fieldName] = $fieldMetadata->getBoost();
        }

        foreach ($entityMetadata->getCopyFields() as $copyField) {
            $this->fields[$copyField->getName()] = $copyField->getSolrName();
        }
    }

    /**
     * Generate a document ID for an entity.
     *
     * @param $entity
     */
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
     * Map an entity to a solr document.
     *
     * @param $entity
     *
     * @return Document
     */
    public function toDocument($entity) {
        if ( ! $entity) {
            return;
        }
        $class = ClassUtils::getClass($entity);
        if ( ! ($entityMeta = ($this->map[$class] ?? null))) {
            $this->logger->warning('Cannot index unknown class {class}.', [
                'class' => $class,
            ]);

            return;
        }
        $document = new Document();
        $document->setKey($entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity));
        $document->setField('id', $entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity));
        $document->setField('class_s', $entityMeta->getClass());

        foreach ($entityMeta->getFixed() as $key => $value) {
            $document->setField($key, $value);
        }

        foreach ($entityMeta->getFieldMetadata() as $fieldMetadata) {
            $data = $fieldMetadata->fetch($entity);
            $data = preg_replace('/(?:^\\s*|\\s*$)/u', '', $data);
            $document->setField($fieldMetadata->getSolrName(), preg_replace('/\\s{2,}/u', ' ', $data));
            $boost = $fieldMetadata->getBoost();
            if ($boost && 1.0 !== $boost) {
                $document->setFieldBoost($fieldMetadata->getSolrName(), $boost);
            }
        }

        foreach ($entityMeta->getCopyFields() as $copyField) {
            $to = $copyField->getSolrName();
            $v = [];

            foreach ($copyField->getFrom() as $from) {
                $data = $document->{$from};
                if ( ! $data) {
                    continue;
                }
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

    /**
     * Get the solr field name based on a field name as defined in the.
     *
     * @field/name attribute.
     *
     * @param $name
     */
    public function getSolrName($name) : ?string {
        if ( ! isset($this->fields[$name])) {
            $this->logger->warning('Cannot get solr name for unknown property {name}.', [
                'name' => $name,
            ]);

            return null;
        }

        return $this->fields[$name];
    }

    public function getBoost($name) {
        if( ! isset($this->boosts[$name])) {
            $this->logger->warning('Cannot get boost for unknown property {name}.', [
                'name' => $name,
            ]);
            return null;
        }
        return $this->boosts[$name];
    }

    /**
     * Get the metadata for an entity class.
     *
     * @param mixed $class class name string or object
     */
    public function getEntityMetadata($class) : ?EntityMetadata {
        if (is_object($class)) {
            $class = ClassUtils::getRealClass($class);
        }
        if ( ! isset($this->map[$class])) {
            $this->logger->warning('Cannot get entity metadata for unknown class {class}.', [
                'class' => $class,
            ]);

            return null;
        }

        return $this->map[$class];
    }

    /**
     * Check if an entity is mapped.
     *
     * @param $entity
     *
     * @return bool
     */
    public function isMapped($entity) {
        if ( ! $entity) {
            return false;
        }
        $class = ClassUtils::getClass($entity);

        return array_key_exists($class, $this->map);
    }

    /**
     * List the classes known to the entity mapper.
     *
     * @return array
     */
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
