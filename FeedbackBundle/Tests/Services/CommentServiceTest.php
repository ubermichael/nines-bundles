<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Tests\Services;

use Exception;
use Nines\BlogBundle\Entity\Page;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\UtilBundle\TestCase\ServiceTestCase;
use stdClass;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommentServiceTest extends ServiceTestCase {
    private ?CommentService $service = null;

    public function testSetup() : void {
        $this->assertInstanceOf(CommentService::class, $this->service);
    }

    public function testFindComments() : void {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(false);
        $this->service->setAuthorizationChecker($checker);

        $entity = $this->em->find(Page::class, 1);
        $comments = $this->service->findComments($entity);
        $this->assertCount(0, $comments);
    }

    public function testFindCommentsException() : void {
        $this->expectException(Exception::class);
        $comments = $this->service->findComments([]);
        $this->assertCount(0, $comments);
    }

    public function testFindCommentsAdmin() : void {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(true);
        $this->service->setAuthorizationChecker($checker);

        $comments = $this->service->findComments(stdClass::class . ':1');
        $this->assertCount(1, $comments);
    }

    public function testAddComment() : void {
        $entity = $this->em->find(Page::class, 1);
        $comment = new Comment();
        $comment->setFullname('Bobby Tables');
        $comment->setFollowUp(false);
        $comment->setContent('Comment 1');
        $comment->setEmail('bob@example.com');
        $result = $this->service->addComment($entity, $comment);
        $this->em->flush(); // Services do not flush automatically.
        $this->assertNotNull($result->getId());
    }

    public function testGetForm() : void {
        $this->assertNotNull($this->service->getForm());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->service = self::$container->get(CommentService::class);
    }
}
