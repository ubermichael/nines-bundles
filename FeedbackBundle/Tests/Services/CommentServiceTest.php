<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Tests\Services;

use Nines\BlogBundle\Entity\Page;
use Nines\FeedbackBundle\DataFixtures\CommentFixtures;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\UtilBundle\Tests\ServiceBaseCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommentServiceTest extends ServiceBaseCase
{
    /**
     * @var CommentService
     */
    private $service;

    protected function fixtures() : array {
        return [
            CommentFixtures::class,
        ];
    }

    public function testSanity() : void {
        $this->assertInstanceOf(CommentService::class, $this->service);
    }

    public function testFindEntity() : void {
        $comment = $this->getReference('comment.1');
        $entity = $this->service->findEntity($comment);
        $this->assertNotNull($entity);
        $this->assertInstanceOf(Page::class, $entity);
        $this->assertSame(2, $entity->getId());
    }

    public function testEntityType() : void {
        $comment = $this->getReference('comment.1');
        $entityType = $this->service->entityType($comment);
        $this->assertSame('Page', $entityType);
    }

    public function testEntityUrl() : void {
        $comment = $this->getReference('comment.1');
        $entityUrl = $this->service->entityUrl($comment);
        $this->assertStringEndsWith('/page/2', $entityUrl);
    }

    public function testFindComments() : void {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(false);
        $this->service->setAuthorizationChecker($checker);

        $entity = $this->entityManager->find(Page::class, 2);
        $comments = $this->service->findComments($entity);
        $this->assertCount(0, $comments);
    }

    public function testFindCommentsAdmin() : void {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(true);
        $this->service->setAuthorizationChecker($checker);

        $entity = $this->entityManager->find(Page::class, 2);
        $comments = $this->service->findComments($entity);
        $this->assertCount(1, $comments);
    }

    public function testAddComment() : void {
        $entity = $this->entityManager->find(Page::class, 1);
        $comment = new Comment();
        $comment->setFullname('Bobby');
        $comment->setFollowUp(false);
        $comment->setContent('Comment 1');
        $comment->setEmail('bob@example.com');
        $comment->setTitle('Title 1');
        $result = $this->service->addComment($entity, $comment);
        $this->assertNotNull($result->getId());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->service = $this->getContainer()->get(CommentService::class);
    }
}
