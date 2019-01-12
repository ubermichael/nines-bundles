<?php

namespace Nines\FeedbackBundle\Tests\Services;

use DateTime;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\FeedbackBundle\Services\NotifierService;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Swift_Plugins_MessageLogger;

class NotifierServiceTest extends BaseTestCase {

    /**
     * @var NotifierService
     */
    private $notifier;

    /**
     * @var Swift_Plugins_MessageLogger
     */
    private $messageLogger;

    protected function setUp() {
        parent::setUp();
        $this->notifier = $this->container->get('feedback.notifier');
        $this->messageLogger = $this->container->get('swiftmailer.mailer.default.plugin.messagelogger');
    }

    public function testSanity() {
        $this->assertInstanceOf(NotifierService::class, $this->notifier);
    }

    public function testNoSender() {
        $this->notifier->setSender(false);
        $this->notifier->newComment(new Comment());
        $this->assertEquals(0, $this->messageLogger->countMessages());
    }

    public function testNoRecipient() {
        $this->notifier->setRecipient(false);
        $this->notifier->newComment(new Comment());
        $this->assertEquals(0, $this->messageLogger->countMessages());
    }

    public function testOneRecipient() {
        $comment = $this->createMock(Comment::class);
        $comment->method('getFullname')->willReturn('Alicia');
        $comment->method('getFollowUp')->willReturn(false);
        $comment->method('getCreated')->willReturn(new DateTime());
        $comment->method('getContent')->willReturn("This is a comment.");
        $service = $this->createMock(CommentService::class);
        $service->method('entityUrl')->willReturn("http://example.com/foo");

        $this->notifier->setRecipient("b@example.com");
        $this->notifier->setSender("b@b.com");
        $this->notifier->setService($service);
        $this->notifier->newComment($comment);
        $this->assertEquals(1, $this->messageLogger->countMessages());
    }

    public function testMultiplerecipients() {
        $comment = $this->createMock(Comment::class);
        $comment->method('getFullname')->willReturn('Alicia');
        $comment->method('getFollowUp')->willReturn(false);
        $comment->method('getCreated')->willReturn(new DateTime());
        $comment->method('getContent')->willReturn("This is a comment.");
        $service = $this->createMock(CommentService::class);
        $service->method('entityUrl')->willReturn("http://example.com/foo");

        $this->notifier->setRecipient(["b@example.com", 'c@example.com']);
        $this->notifier->setSender("b@b.com");
        $this->notifier->setService($service);
        $this->notifier->newComment($comment);
        $this->assertEquals(2, $this->messageLogger->countMessages());
    }

}
