<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Tests\Entity;

use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Entity\Value;
use PHPUnit\Framework\TestCase;

class ElementTest extends TestCase {
    public function testAddValue() : void {
        $element = new Element();
        $this->assertCount(0, $element->getValues());
        $value = new Value();
        $value->setData('abc');
        $element->addValue(($value));
        $this->assertCount(1, $element->getValues());
    }

    public function testAddDuplicateValue() : void {
        $element = new Element();
        $this->assertCount(0, $element->getValues());
        $value = new Value();
        $value->setData('abc');
        $element->addValue($value);
        $element->addValue($value);
        $this->assertCount(1, $element->getValues());
    }

    public function testRemoveValue() : void {
        $element = new Element();
        $value = new Value();
        $value->setData('abc');
        $element->addValue($value);
        $element->removeValue($value);
        $this->assertCount(0, $element->getValues());
    }

    public function testRemoveUnaddedValue() : void {
        $element = new Element();
        $value = new Value();
        $value->setData('abc');
        $element->removeValue($value);
        $this->assertCount(0, $element->getValues());
    }
}
