<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Tests\Service;

use Nines\DublinCoreBundle\Service\ValueManager;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class ValueManagerTest extends ServiceTestCase {
    private ?ValueManager $manager = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(ValueManager::class, $this->manager);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->manager = self::$container->get(ValueManager::class);
    }
}
