<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Annotation;
use Exception;
use Nines\SolrBundle\Annotation\ComputedField;
use Nines\SolrBundle\Annotation\CopyField;
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Annotation\Id;
use Nines\SolrBundle\Exception\MappingException;
use Nines\SolrBundle\Logging\SolrLogger;
use Nines\SolrBundle\Metadata\CopyFieldMetadata;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\SolrBundle\Metadata\FieldMetadata;
use Nines\SolrBundle\Metadata\IdMetadata;
use ReflectionClass;
use ReflectionProperty;

/**
 * Construct the EntityMapper by parsing annotations on entities.
 */
class EntityMapperFactory {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $env;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var SolrLogger
     */
    private $logger;

    /**
     * @var EntityMapper
     */
    private static $mapper;

    /**
     * @param string $env
     * @param string $cacheDir
     */
    public function __construct($env, $cacheDir) {
        $this->env = $env;
        $this->cacheDir = $cacheDir;
    }

    /**
     * Create the mapper by parsing the entity properties and annotations.
     *
     * @todo refactor this method
     *
     * @throws Exception
     */
    private function createMapper(SolrLogger $logger) : EntityMapper {
        $mapper = new EntityMapper();
        $mapper->setSolrLogger($logger);

        AnnotationRegistry::registerLoader('class_exists');
        $reader = new CachedReader(
            new AnnotationReader(),
            new PhpFileCache($this->cacheDir . '/solr_annotations'),
            ('prod' !== $this->env)
        );

        $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
            $reflectionClass = $meta->getReflectionClass();
            $classAnnotation = $reader->getClassAnnotation($reflectionClass, Document::class);
            if ( ! $classAnnotation) {
                continue;
            }
            $properties = $this->getProperties($reflectionClass);

            $entityMeta = new EntityMetadata();
            $entityMeta->setClass($meta->getName());
            $entityMeta->addFixed('type_s', $reflectionClass->getShortName());

            /** @var ReflectionProperty $idProperty */
            /** @var Annotation $idAnnotation */
            list($idAnnotation, $idProperty) = $this->getIdProperty($reader, $properties);
            $idMeta = $this->analyzeIdField($idProperty, $idAnnotation);
            $entityMeta->setId($idMeta);

            $solrNames = [];

            foreach ($properties as $property) {
                $propertyAnnotation = $reader->getPropertyAnnotation($property, Field::class);
                if ( ! $propertyAnnotation) {
                    continue;
                }
                $fieldMeta = $this->analyzeField($property, $propertyAnnotation);
                $entityMeta->addFieldMetadata($fieldMeta);
                $solrNames[$property->getName()] = $fieldMeta->getSolrName();
            }

            foreach ($classAnnotation->computedFields as $computedField) {
                $fieldMeta = $this->analyzeComputedField($computedField);
                $entityMeta->addFieldMetadata($fieldMeta);
                $solrNames[$computedField->name] = $fieldMeta->getSolrName();
            }

            // do the copy fields after the regular fields have been set up.
            foreach ($classAnnotation->copyField as $copyField) {
                $fieldMeta = $this->analyzeCopyField($copyField, $solrNames);

                $entityMeta->addCopyField($fieldMeta);
                $solrNames[$copyField->to] = $fieldMeta->getSolrName();
            }

            $mapper->addEntity($entityMeta);
        }

        return $mapper;
    }

    /**
     * Get the ID property defined in an entity. Returns the annotation
     * that defined the ID and the ReflectionProperty that the annotation is
     * defined on.
     *
     * @param AnnotationReader $reader
     * @param ReflectionProperty[] $reflectionProperties
     *
     * @throws Exception if two or more IDs are defined on an entity
     *
     * @return array of Id, ReflectionProperty
     */
    public function getIdProperty($reader, $reflectionProperties) {
        $idProperty = null;
        $idAnnotation = null;

        foreach ($reflectionProperties as $reflectionProperty) {
            $annotation = $reader->getPropertyAnnotation($reflectionProperty, Id::class);
            if ( ! $annotation) {
                continue;
            }
            if ($idProperty) {
                throw new MappingException('Cannot have two identifiers in ' . $reflectionProperty->getDeclaringClass()->getName());
            }
            $idProperty = $reflectionProperty;
            if ( ! $idAnnotation) {
                $idAnnotation = $annotation;
            }
        }

        return [$idAnnotation, $idProperty];
    }

    /**
     * Get the reflection properties for a class and all its ancestors.
     *
     * @return ReflectionProperty[]
     */
    public function getProperties(ReflectionClass $rc) {
        $properties = [];
        do {
            foreach ($rc->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }
        } while ($rc = $rc->getParentClass());

        return $properties;
    }

    public function analyzeIdField(ReflectionProperty $property, Id $id) {
        $idMeta = new IdMetadata();
        $idMeta->setName($property->getName());
        $idMeta->setGetter($id->getter ?? 'get' . ucfirst($property->getName()));

        return $idMeta;
    }

    public function analyzeField(ReflectionProperty $property, Field $field) {
        $suffix = Field::TYPE_MAP[$field->type];
        if ( ! $suffix) {
            throw new MappingException('Unknown solr type ' . $field->type);
        }
        if ($field->name) {
            $solrName = $field->name;
        } else {
            $solrName = mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName()));
        }
        if (mb_substr($solrName, -1 * mb_strlen($suffix)) !== $suffix) {
            $solrName .= $suffix;
        }

        $fieldMeta = new FieldMetadata();
        $fieldMeta->setSolrName($solrName);
        $fieldMeta->setBoost($field->boost);
        $fieldMeta->setFieldName($property->getName());
        $fieldMeta->setGetter($field->getter ?? 'get' . ucfirst($property->getName()));
        $fieldMeta->setMutator($field->mutator);
        $fieldMeta->setFilters($field->filters);

        return $fieldMeta;
    }

    public function analyzeComputedField(ComputedField $computedField) {
        $solrName = $computedField->name . Field::TYPE_MAP[$computedField->type];
        $fieldMeta = new FieldMetadata();
        $fieldMeta->setSolrName($solrName);
        $fieldMeta->setBoost($computedField->boost);
        $fieldMeta->setFieldName($computedField->name);
        $fieldMeta->setGetter($computedField->getter);

        return $fieldMeta;
    }

    public function analyzeCopyField(CopyField $copyField, $solrNames) {
        $fieldMeta = new CopyFieldMetadata();
        $fieldMeta->setName($copyField->to);
        $solrName = $copyField->to . Field::TYPE_MAP[$copyField->type];
        $from = array_map(function ($s) use ($solrNames) {
            return $solrNames[$s];
        }, $copyField->from);
        $fieldMeta->setFrom($from);
        $fieldMeta->setSolrName($solrName);

        return $fieldMeta;
    }

    /**
     * Generate the entity mapper and return it.
     *
     * @throws Exception
     */
    public function build() : EntityMapper {
        if ( ! self::$mapper) {
            self::$mapper = $this->createMapper($this->logger);
        }

        return self::$mapper;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     */
    public function setSolrLogger(SolrLogger $logger) : void {
        $this->logger = $logger;
    }
}
