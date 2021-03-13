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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Annotation;
use Exception;
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Annotation\Id;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\SolrBundle\Metadata\FieldMetadata;
use Nines\SolrBundle\Metadata\IdMetadata;
use ReflectionClass;
use ReflectionProperty;

class EntityMapperBuilder {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var EntityMapper
     */
    private static $mapper;

    /**
     * @param AnnotationReader $reader
     * @param ReflectionProperty[] $reflectionProperties
     *
     * @throws Exception
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

    private function createMapper() : EntityMapper {
        $mapper = new EntityMapper();

        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
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

            foreach ($properties as $property) {
                $propertyAnnotation = $reader->getPropertyAnnotation($property, Field::class);
                if ( ! $propertyAnnotation) {
                    continue;
                }
                $solrName = $propertyAnnotation->name ?? mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName())) . Field::TYPE_MAP[$propertyAnnotation->type];
                $fieldMeta = new FieldMetadata();
                $fieldMeta->setSolrName($solrName);
                $fieldMeta->setFieldName($property->getName());
                $fieldMeta->setGetter($propertyAnnotation->getter ?? 'get' . ucfirst($property->getName()));
                $fieldMeta->setMutator($propertyAnnotation->mutator);
                $fieldMeta->setFilters($propertyAnnotation->filters);
                $entityMeta->addFieldMetadata($fieldMeta);
            }

            $mapper->addEntity($entityMeta);
        }

        return $mapper;
    }

    public function build() : EntityMapper {
        if ( ! self::$mapper) {
            self::$mapper = $this->createMapper();
        }

        return self::$mapper;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }
}
