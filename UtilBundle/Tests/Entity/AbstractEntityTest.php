<?php

namespace Nines\UtilBundle\Tests\Entity;

use Nines\UtilBundle\Entity\AbstractEntity;
use PHPUnit\Framework\TestCase;

class DummyEntity extends AbstractEntity {

    public function __toString() {
        return "dummy";
    }

}

class AbstractEntityTest extends TestCase {

    public function testSetCreated() {
        $obj = new DummyEntity();
        $obj->prePersist();
        $dateTime = $obj->getCreated();
        $obj->prePersist();
        $this->assertEquals($dateTime, $obj->getCreated());
    }

}
