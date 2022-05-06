<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
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
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

/**
 * Construct the EntityMapper by parsing annotations on entities.
 */
class EntityMapperFactory {
    private ?EntityManagerInterface $em = null;

    private ?string $env = null;

    private ?SolrLogger $logger = null;

    private static ?EntityMapper $mapper = null;

    public function __construct(string $env) {
        $this->env = $env;
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
        $cache = new PhpFilesAdapter('doctrine_queries');
        $reader = new PsrCachedReader(new AnnotationReader(), $cache, ('prod' !== $this->env));
        $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($this->em->getMetadataFactory()->getAllMetadata() as $meta) {
            $reflectionClass = $meta->getReflectionClass();
            $entityMeta = $this->getEntityMetadata($reflectionClass, $reader);
            if ($entityMeta) {
                $mapper->addEntity($entityMeta);
            }
        }

        return $mapper;
    }

    /**
     * @throws Exception
     * @throws MappingException
     */
    public function getEntityMetadata(ReflectionClass $reflectionClass, Reader $reader) : ?EntityMetadata {
        $document = $reader->getClassAnnotation($reflectionClass, Document::class);
        if ( ! $document) {
            return null;
        }
        $properties = $this->getProperties($reflectionClass);

        $entityMeta = new EntityMetadata();
        $entityMeta->setClass($reflectionClass->getName());
        $entityMeta->addFixed('type_s', $reflectionClass->getShortName());

        /** @var Id $idAnnotation */
        /** @var ReflectionProperty $idProperty */
        list($idAnnotation, $idProperty) = $this->getIdProperty($reader, $properties);
        $idMeta = $this->analyzeIdField($idAnnotation, $idProperty);
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

        foreach ($document->computedFields as $computedField) {
            $fieldMeta = $this->analyzeComputedField($computedField);
            $entityMeta->addFieldMetadata($fieldMeta);
            $solrNames[$computedField->name] = $fieldMeta->getSolrName();
        }

        // do the copy fields after the regular fields have been set up.
        foreach ($document->copyField as $copyField) {
            $fieldMeta = $this->analyzeCopyField($copyField, $solrNames);

            $entityMeta->addCopyField($fieldMeta);
            $solrNames[$copyField->to] = $fieldMeta->getSolrName();
        }

        return $entityMeta;
    }

    /**
     * Get the ID property defined in an entity. Returns the annotation
     * that defined the ID and the ReflectionProperty that the annotation is
     * defined on.
     *
     * @param ReflectionProperty[] $reflectionProperties
     *
     * @throws Exception if two or more IDs are defined on an entity
     *
     * @return array<int,mixed>
     */
    public function getIdProperty(Reader $reader, array $reflectionProperties) : array {
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
    public function getProperties(ReflectionClass $rc) : array {
        $properties = [];
        do {
            foreach ($rc->getProperties() as $property) {
                $properties[$property->getName()] = $property;
            }
        } while ($rc = $rc->getParentClass());

        return $properties;
    }

    public function analyzeIdField(Id $id, ReflectionProperty $property) : IdMetadata {
        $idMeta = new IdMetadata();
        $idMeta->setName($property->getName());
        $idMeta->setGetter($id->getter ?? 'get' . ucfirst($property->getName()));

        return $idMeta;
    }

    /**
     * @throws MappingException
     */
    public function analyzeField(ReflectionProperty $property, Field $field) : FieldMetadata {
        if ( ! array_key_exists($field->type, Field::TYPE_MAP)) {
            throw new MappingException('Unknown solr type ' . $field->type);
        }
        $suffix = Field::TYPE_MAP[$field->type];
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

    public function analyzeComputedField(ComputedField $computedField) : FieldMetadata {
        $solrName = $computedField->name . Field::TYPE_MAP[$computedField->type];
        $fieldMeta = new FieldMetadata();
        $fieldMeta->setSolrName($solrName);
        $fieldMeta->setBoost($computedField->boost);
        $fieldMeta->setFieldName($computedField->name);
        $fieldMeta->setGetter($computedField->getter);

        return $fieldMeta;
    }

    /**
     * @param array<string,string> $solrNames
     */
    public function analyzeCopyField(CopyField $copyField, array $solrNames) : CopyFieldMetadata {
        $fieldMeta = new CopyFieldMetadata();
        $fieldMeta->setName($copyField->to);
        $solrName = $copyField->to . Field::TYPE_MAP[$copyField->type];
        $from = array_map(fn($s) => $solrNames[$s], $copyField->from);
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
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setSolrLogger(SolrLogger $logger) : void {
        $this->logger = $logger;
    }
}
