<?php

namespace Nines\FeedbackBundle\Tests\Services;

use Nines\BlogBundle\Entity\Page;
use Nines\FeedbackBundle\DataFixtures\ORM\LoadComment;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CommentServiceTest extends BaseTestCase {

    /**
     * @var CommentService
     */
    private $service;

    protected function setUp() {
        parent::setUp();
        $this->service = $this->container->get('feedback.comment');
    }

    public function getFixtures() {
        return array(
            LoadComment::class,
        );
    }

    public function testSanity() {
        $this->assertInstanceOf(CommentService::class, $this->service);
    }

    public function testFindEntity() {
        $comment = $this->getReference('comment.1');
        $entity = $this->service->findEntity($comment);
        $this->assertNotNull($entity);
        $this->assertInstanceOf(Page::class, $entity);
        $this->assertEquals(2, $entity->getId());
    }

    public function testEntityType() {
        $comment = $this->getReference('comment.1');
        $entityType = $this->service->entityType($comment);
        $this->assertEquals('Page', $entityType);
    }


    public function testEntityUrl() {
        $comment = $this->getReference('comment.1');
        $entityUrl = $this->service->entityUrl($comment);
        $this->assertStringEndsWith('/page/2', $entityUrl);
    }

    public function testFindComments() {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(false);
        $this->service->setAuthorizationChecker($checker);

        $entity = $this->em->find(Page::class, 2);
        $comments = $this->service->findComments($entity);
        $this->assertCount(0, $comments);
    }

    public function testFindCommentsAdmin() {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->method('isGranted')->willReturn(true);
        $this->service->setAuthorizationChecker($checker);

        $entity = $this->em->find(Page::class, 2);
        $comments = $this->service->findComments($entity);
        $this->assertCount(1, $comments);
    }

    public function testAddComment() {
        $entity = $this->em->find(Page::class, 1);
        $comment = new Comment();
        $comment->setFullname('Bobby');
        $comment->setFollowUp(false);
        $comment->setContent("Comment 1");
        $comment->setEmail('bob@example.com');
        $comment->setTitle("Title 1");
        $result = $this->service->addComment($entity, $comment);
        $this->assertNotNull($result->getId());
    }
}
