<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\TestCase;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class ServiceTestCase extends KernelTestCase {
    protected ?EntityManagerInterface $em = null;

    protected function setUp() : void {
        parent::setUp();
        if (getenv('TEST_DISABLE_DEBUG')) {
            self::bootKernel(['debug' => false]);
        } else {
            self::bootKernel();
        }
        $this->em = self::$container->get(EntityManagerInterface::class);
    }
}
