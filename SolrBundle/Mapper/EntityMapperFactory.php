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
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Annotation\Id;
use Nines\SolrBundle\Logging\SolrLogger;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\SolrBundle\Metadata\FieldMetadata;
use Nines\SolrBundle\Metadata\IdMetadata;
use ReflectionClass;
use ReflectionProperty;

/**
 * Construct the EntityMapper by parsing annotations on entities.
 */
class EntityMapperFactory
{
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
     * Get the ID property defined in an entity. Returns the annotation
     * that defined the ID and the ReflectionProperty that the annotation is
     * defined on.
     *
     * @param AnnotationReader $reader
     * @param ReflectionProperty[] $reflectionProperties
     *
     * @throws Exception if two or more IDs are defined on an entity
     *
     * @return array of Annotation, ReflectionProperty
     */
    private function getIdProperty($reader, $reflectionProperties) {
        $idProperty = null;
        $annotation = null;

        foreach ($reflectionProperties as $reflectionProperty) {
            $annotation = $reader->getPropertyAnnotation($reflectionProperty, Id::class);
            if ( ! $annotation) {
                continue;
            }
            if ($idProperty) {
                throw new Exception('Cannot have two identifiers in ' . $reflectionProperty->getDeclaringClass()->getName());
            }
            $idProperty = $reflectionProperty;
        }

        return [$annotation, $idProperty];
    }

    /**
     * Get the reflection properties for a class and all its ancestors.
     *
     * @return ReflectionProperty[]
     */
    private function getProperties(ReflectionClass $rc) {
        $properties = [];
        do {
            foreach ($rc->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }
        } while ($rc = $rc->getParentClass());

        return $properties;
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
            $debug = ('prod' !== $this->env),
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
            $idMeta = new IdMetadata();
            $idMeta->setName($idProperty->getName());
            $idMeta->setGetter($idAnnotation->getter ?? 'get' . ucfirst($idProperty->getName()));
            $entityMeta->setId($idMeta);

            $solrNames = [];

            foreach ($properties as $property) {
                $propertyAnnotation = $reader->getPropertyAnnotation($property, Field::class);
                if ( ! $propertyAnnotation) {
                    continue;
                }

                $suffix = Field::TYPE_MAP[$propertyAnnotation->type];
                if( ! $suffix) {
                    throw new Exception("Unknown solr type " . $propertyAnnotation->type);
                }
                if($propertyAnnotation->name) {
                    $solrName = $propertyAnnotation->name;
                } else {
                    $solrName = mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName()));
                }
                if( substr($solrName, -1*strlen($suffix)) !== $suffix) {
                    $solrName .= $suffix;
                }

                $fieldMeta = new FieldMetadata();
                $fieldMeta->setSolrName($solrName);
                $fieldMeta->setBoost($propertyAnnotation->boost);
                $fieldMeta->setFieldName($property->getName());
                $fieldMeta->setGetter($propertyAnnotation->getter ?? 'get' . ucfirst($property->getName()));
                $fieldMeta->setMutator($propertyAnnotation->mutator);
                $fieldMeta->setFilters($propertyAnnotation->filters);
                $entityMeta->addFieldMetadata($fieldMeta);
                $solrNames[$property->getName()] = $solrName;
            }

            foreach ($classAnnotation->computedFields as $computedField) {
                $solrName = $computedField->name . Field::TYPE_MAP[$computedField->type];
                $fieldMeta = new FieldMetadata();
                $fieldMeta->setSolrName($solrName);
                $fieldMeta->setBoost($computedField->boost);
                $fieldMeta->setFieldName($computedField->name);
                $fieldMeta->setGetter($computedField->getter);
                $entityMeta->addFieldMetadata($fieldMeta);
                $solrNames[$computedField->name] = $solrName;
            }

            // do the copy fields after the regular fields have been set up.
            foreach ($classAnnotation->copyField as $copyField) {
                $solrName = $copyField->to . Field::TYPE_MAP[$copyField->type];
                $from = array_map(function ($s) use ($solrNames) {return $solrNames[$s]; }, $copyField->from);
                $entityMeta->addCopyFields($from, $copyField->to, $solrName);
                $solrNames[$copyField->to] = $solrName;
            }

            $mapper->addEntity($entityMeta);
        }

        return $mapper;
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
