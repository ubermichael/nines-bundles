<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Tests\Services;

use DateTimeImmutable;
use Exception;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\FeedbackBundle\Services\NotifierService;
use Nines\UtilBundle\Tests\ServiceBaseCase;
use Symfony\Component\Mailer\MailerInterface;

class NotifierServiceTest extends ServiceBaseCase
{
    /**
     * @var NotifierService
     */
    private $notifier;

    /**
     * @return CommentService
     */
    private function getMockService() {
        /** @var CommentService $service */
        $service = $this->createMock(CommentService::class);
        $service->method('entityUrl')->willReturn('http://example.com/foo');

        return $service;
    }

    /**
     * @throws Exception
     *
     * @return Comment
     */
    private function getMockComment() {
        /** @var Comment $comment */
        $comment = $this->createMock(Comment::class);
        $comment->method('getFullname')->willReturn('Alicia');
        $comment->method('getFollowUp')->willReturn(false);
        $comment->method('getCreated')->willReturn(new DateTimeImmutable());
        $comment->method('getContent')->willReturn('This is a comment.');

        return $comment;
    }

    private function getMockMailer() {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->method('send')->willReturnArgument(0);

        return $mailer;
    }

    public function testSanity() : void {
        $this->assertInstanceOf(NotifierService::class, $this->notifier);
    }

    public function testNoSender() : void {
        $comment = $this->getMockComment();

        $this->notifier->setSender(false);
        $result = $this->notifier->newComment($comment);
        $this->assertNull($result);
    }

    public function testNoRecipient() : void {
        $comment = $this->getMockComment();

        $this->notifier->setRecipients(false);
        $this->notifier->newComment($comment);
        $result = $this->notifier->newComment($comment);
        $this->assertNull($result);
    }

    public function testSent() : void {
        $comment = $this->getMockComment();
        $service = $this->getMockService();

        $this->notifier->setRecipients('b@example.com');
        $this->notifier->setSender('b@b.com');
        $this->notifier->setService($service);

        $email = $this->notifier->newComment($comment);
        $this->assertNotNull($email);
        $this->assertCount(1, $email->getFrom());
        $this->assertSame('b@b.com', $email->getFrom()[0]->getAddress());

        $this->assertCount(1, $email->getBcc());
        $this->assertSame('b@example.com', $email->getBcc()[0]->getAddress());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->notifier = $this->getContainer()->get(NotifierService::class);
    }
}
