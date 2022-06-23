<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Tests\Entity;

use Nines\DublinCoreBundle\Entity\Value;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase {
    private ?Value $value = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Value::class, $this->value);
    }

    public function testToString() : void {
        $this->assertSame('', "{$this->value}");
        $this->value->setData('Hello');
        $this->assertSame('Hello', "{$this->value}");
    }

    protected function setUp() : void {
        parent::setUp();
        $this->value = new Value();
    }
}
