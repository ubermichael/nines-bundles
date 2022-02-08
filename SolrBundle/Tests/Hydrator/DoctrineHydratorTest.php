<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Hydrator;

use Nines\SolrBundle\Hydrator\DoctrineHydrator;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\TestCase\ServiceTestCase;
use stdClass;

class DoctrineHydratorTest extends ServiceTestCase {
    private DoctrineHydrator $hydrator;

    public function testSetUp() : void {
        $this->assertInstanceOf(DoctrineHydrator::class, $this->hydrator);
    }

    public function testHydrate() : void {
        $document = new stdClass();
        $document->id = User::class . ':' . 1;
        $user = $this->hydrator->hydrate($document);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('admin@example.com', $user->getEmail());
    }

    protected function setUp() : void {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->hydrator = self::$container->get(DoctrineHydrator::class);
    }
}
