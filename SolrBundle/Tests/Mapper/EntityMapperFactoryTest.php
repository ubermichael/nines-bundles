<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Mapper;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Nines\SolrBundle\Mapper\EntityMapperFactory;
use Nines\SolrBundle\Tests\Client\ClientBaseCase;
use Nines\SolrBundle\Tests\Fixtures\Entity;
use ReflectionClass;

class EntityMapperFactoryTest extends ClientBaseCase {
    public function testGetProperties() : void {
        $emf = $this->getContainer()->get(EntityMapperFactory::class);
        AnnotationRegistry::registerLoader('class_exists');
        $refClass = new ReflectionClass(Entity::class);
        $properties = $emf->getProperties($refClass);
        $this->assertCount(8, $properties);

        // test that the parent entity id is found.
        $this->assertArrayHasKey('id', $properties);
        $this->assertSame('id', $properties['id']->getName());

        // check for a property in the entity.
        $this->assertArrayHasKey('date', $properties);
        $this->assertSame('date', $properties['date']->getName());
    }

    public function testGetId() : void {
        $emf = $this->getContainer()->get(EntityMapperFactory::class);
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
}
