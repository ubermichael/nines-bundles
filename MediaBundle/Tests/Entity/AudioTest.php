<?php

declare(strict_types=1);

namespace Nines\MediaBundle\Tests\Entity;

use Nines\MediaBundle\Entity\Audio;
use PHPUnit\Framework\TestCase;

class AudioTest extends TestCase {
    private ?Audio $audio = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Audio::class, $this->audio);
    }


    protected function setUp() : void {
        parent::setUp();
        $this->audio = new Audio();
    }
}
