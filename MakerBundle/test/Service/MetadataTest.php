<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MakerBundle\Test\Service;

use Exception;
use Nines\MakerBundle\Service\Metadata;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MetadataTest extends KernelTestCase {
    private ?Metadata $service = null;

    /**
     * @test
     */
    public function config() : void {
        $this->assertNotNull($this->service);
    }

    /**
     * @throws Exception
     */
    protected function setUp() : void {
        self::bootKernel();
        $container = static::getContainer();
        $this->service = $container->get(Metadata::class);
    }
}
