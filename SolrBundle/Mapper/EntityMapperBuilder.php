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
use Exception;
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Annotation\Id;
use ReflectionClass;
use ReflectionProperty;

class EntityMapperBuilder {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $copyFields;

    /**
     * @var null|EntityMapper
     */
    private static $entityMapper;

    /**
     * @param ReflectionProperty[] $properties
     *
     * @throws Exception
     * @return ReflectionProperty
     */
    private function getIdentifier($properties) : ?ReflectionProperty {
        $identifier = null;
        $reader = new AnnotationReader();

        foreach ($properties as $property) {
            $annotation = $reader->getPropertyAnnotation($property, Id::class);
            if ( ! $annotation) {
                continue;
            }
            if ($identifier) {
                throw new Exception('Cannot have two identifiers in ' . $property->getDeclaringClass()->getName());
            }
            $identifier = $property;
        }
        return $identifier;
    }

    /**
     * @param ReflectionClass $rc
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

    private function createMapping() {
        $mapping = new EntityMapper();
        $mapping->setCopyFields($this->copyFields);

        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();

        foreach ($metadata as $meta) {
            $reflection = $meta->getReflectionClass();
            $classAnnotation = $reader->getClassAnnotation($reflection, Document::class);

            if ( ! $classAnnotation) {
                continue;
            }

            $properties = $this->getProperties($reflection);
            $identifier = $this->getIdentifier($properties);
            $mapping->addClass($meta->getName());

            $idAnnotation = $reader->getPropertyAnnotation($identifier, Id::class);
            $mapping->addId($meta->getName(), $identifier->getName(), [
                'getter' => $idAnnotation->getter ?? 'get' . ucfirst($identifier->getName()),
            ]);

            foreach ($properties as $property) {
                $propAnnotation = $reader->getPropertyAnnotation($property, Field::class);
                if ( ! $propAnnotation) {
                    continue;
                }
                $fieldName = $propAnnotation->name ?? mb_strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $property->getName()));
                $fieldName .= Field::TYPE_MAP[$propAnnotation->type];
                $mapping->addField($meta->getName(), $property->getName(), $fieldName, [
                    'mutator' => $propAnnotation->mutator,
                    'getter' => $propAnnotation->getter ?? 'get' . ucfirst($property->getName()),
                ]);
            }
        }

        return $mapping;
    }

    public function build() {
        if ( ! self::$entityMapper) {
            self::$entityMapper = $this->createMapping();
        }

        return self::$entityMapper;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    public function setCopyFields($copyFields) : void {
        $this->copyFields = $copyFields;
    }
}
