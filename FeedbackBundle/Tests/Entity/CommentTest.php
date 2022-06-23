<?php

declare(strict_types=1);

namespace Nines\FeedbackBundle\Tests\Entity;

use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\CommentNote;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase {
    private ?Comment $comment = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(Comment::class, $this->comment);
    }

    public function testRemoveNote() : void {
        $note = new CommentNote();
        $this->comment->addNote($note);
        $this->comment->removeNote($note);
        $this->assertCount(0, $this->comment->getNotes());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->comment = new Comment();
    }
}
