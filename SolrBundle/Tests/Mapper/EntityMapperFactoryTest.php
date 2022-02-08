<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Mapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Nines\SolrBundle\Annotation\Document;
use Nines\SolrBundle\Annotation\Field;
use Nines\SolrBundle\Mapper\EntityMapperFactory;
use Nines\SolrBundle\TestUtil\Fixtures\ComplexId;
use Nines\SolrBundle\TestUtil\Fixtures\Entity;
use Nines\UtilBundle\TestCase\ServiceTestCase;
use ReflectionClass;

class EntityMapperFactoryTest extends ServiceTestCase {
    public function testGetProperties() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $this->assertCount(9, $properties);

        // test that the parent entity id is found.
        $this->assertArrayHasKey('id', $properties);
        $this->assertSame('id', $properties['id']->getName());

        // check for a property in the entity.
        $this->assertArrayHasKey('date', $properties);
        $this->assertSame('date', $properties['date']->getName());
    }

    public function testGetIdProperty() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();

        list($idAnn, $idProp) = $emf->getIdProperty($reader, $properties);
        $this->assertNotNull($idAnn);
        $this->assertNull($idAnn->name);
        $this->assertNull($idAnn->getter);

        $this->assertNotNull($idProp);
    }

    public function testGetParentProperties() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);

        $this->assertArrayHasKey('something', $properties);
    }

    public function testAnalyzeIdField() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(ComplexId::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();

        list($idAnn, $idProp) = $emf->getIdProperty($reader, $properties);
        $meta = $emf->analyzeIdField($idAnn, $idProp);
        $this->assertSame('id', $meta->getName());
        $this->assertSame('idGetter', $meta->getGetter());
        $this->assertSame(['abc', '1', 'true'], $meta->getGetterArgs());
    }

    public function testAnalyzeSimpleField() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();

        $property = $properties['name'];
        $meta = $emf->analyzeField($property, $reader->getPropertyAnnotation($property, Field::class));

        $this->assertSame('name', $meta->getFieldName());
        $this->assertSame('name_t', $meta->getSolrName());
        $this->assertSame(2.0, $meta->getBoost());
        $this->assertSame('getName', $meta->getGetter());
        $this->assertSame([], $meta->getGetterArgs());
        $this->assertNull($meta->getMutator());
        $this->assertSame([], $meta->getMutatorArgs());
        $this->assertSame([], $meta->getFilters());
        $this->assertSame([], $meta->getFilterArgs());
    }

    public function testAnalyzeFieldGetter() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();

        $property = $properties['date'];
        $meta = $emf->analyzeField($property, $reader->getPropertyAnnotation($property, Field::class));

        $this->assertSame('date_dt', $meta->getSolrName());
        $this->assertSame('format', $meta->getMutator());
        $this->assertSame(['Y-m-d\\TH:i:sP'], $meta->getMutatorArgs());
    }

    public function testAnalyzeFieldFilters() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();

        $property = $properties['content'];
        $meta = $emf->analyzeField($property, $reader->getPropertyAnnotation($property, Field::class));

        $this->assertSame('content_t', $meta->getSolrName());
        $this->assertSame(['strip_tags', 'html_entity_decode'], $meta->getFilters());
        $this->assertSame([[], ['51', 'UTF-8']], $meta->getFilterArgs());
    }

    public function testAnalyzeComputedField() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();
        $classAnnotation = $reader->getClassAnnotation($refClass, Document::class);

        $this->assertCount(1, $classAnnotation->computedFields);
        $meta = $emf->analyzeComputedField($classAnnotation->computedFields[0]);
        $this->assertSame('coordinates', $meta->getFieldName());
        $this->assertSame('coordinates_p', $meta->getSolrName());
        $this->assertNull($meta->getBoost());
        $this->assertSame('getCoordinates', $meta->getGetter());
        $this->assertSame([], $meta->getGetterArgs());
        $this->assertNull($meta->getMutator());
        $this->assertSame([], $meta->getMutatorArgs());
        $this->assertSame([], $meta->getFilters());
        $this->assertSame([], $meta->getFilterArgs());
    }

    public function testAnalyzeCopyField() : void {
        $emf = self::$container->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $reader = new AnnotationReader();
        $classAnnotation = $reader->getClassAnnotation($refClass, Document::class);

        $this->assertCount(1, $classAnnotation->copyField);
        $meta = $emf->analyzeCopyField($classAnnotation->copyField[0], ['name' => 'name_t', 'tags' => 'tags_txt']);
        $this->assertSame('content', $meta->getName());
        $this->assertSame('content_txt', $meta->getSolrName());
    }
}
