<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests\Entity;

use Nines\UtilBundle\Entity\AbstractEntity;
use PHPUnit\Framework\TestCase;

class DummyEntity extends AbstractEntity {
    public function __toString() {
        return 'dummy';
    }
}

class AbstractEntityTest extends TestCase {
    public function testSetCreated() : void {
        $obj = new DummyEntity();
        $obj->prePersist();
        $dateTime = $obj->getCreated();
        $obj->prePersist();
        $this->assertSame($dateTime, $obj->getCreated());
    }
}
