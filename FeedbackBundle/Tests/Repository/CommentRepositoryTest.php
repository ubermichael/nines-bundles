<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Tests\Repository;

use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Repository\CommentRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class CommentRepositoryTest extends ServiceTestCase {
    private ?CommentRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(CommentRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $this->assertCount(5, $this->repo->indexQuery()->execute());
    }

    public function testIndexQueryStatus() : void {
        $status = $this->em->find(CommentStatus::class, 1);
        $this->assertCount(1, $this->repo->indexQuery($status->getName())->execute());
    }

    public function testSearchQuery() : void {
        $this->assertCount(5, $this->repo->searchQuery('paragraph')->execute());
    }

    protected function setUp() : void {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->repo = self::$container->get(CommentRepository::class);
    }
}
