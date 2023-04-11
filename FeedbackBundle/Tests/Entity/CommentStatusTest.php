<?php

declare(strict_types=1);

namespace Nines\FeedbackBundle\Tests\Entity;

use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\CommentStatus;
use PHPUnit\Framework\TestCase;

class CommentStatusTest extends TestCase {
    private ?CommentStatus $commentStatus = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(CommentStatus::class, $this->commentStatus);
    }

    public function testAddComment() : void {
        $comment = new Comment();
        $this->commentStatus->addComment($comment);
        $this->assertCount(1, $this->commentStatus->getComments());
    }

    public function testAddDupComment() : void {
        $comment = new Comment();
        $this->commentStatus->addComment($comment);
        $this->commentStatus->addComment($comment);
        $this->assertCount(1, $this->commentStatus->getComments());
    }

    public function testRemoveComment() : void {
        $comment = new Comment();
        $this->commentStatus->addComment($comment);
        $this->commentStatus->removeComment($comment);
        $this->assertCount(0, $this->commentStatus->getComments());
    }

    public function testRemoveInvalidComment() : void {
        $comment = new Comment();
        $this->commentStatus->removeComment($comment);
        $this->assertCount(0, $this->commentStatus->getComments());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->commentStatus = new CommentStatus();
    }
}
