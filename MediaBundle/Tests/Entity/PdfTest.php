<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Tests\Entity;

use Nines\MediaBundle\Entity\Pdf;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase {
    private ?Pdf $pdf = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Pdf::class, $this->pdf);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->pdf = new Pdf();
    }
}
