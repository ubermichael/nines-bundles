<?php

declare(strict_types=1);

namespace Nines\MediaBundle\Tests\Entity;

use Nines\MediaBundle\Entity\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase {
    private ?Image $image = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Image::class, $this->image);
    }


    protected function setUp() : void {
        parent::setUp();
        $this->image = new Image();
    }
}
