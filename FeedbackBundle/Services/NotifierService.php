<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Services;

use Nines\FeedbackBundle\Entity\Comment;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class NotifierService {
    /**
     * Sender email address.
     *
     * @var string
     */
    private $sender;

    /**
     * List of email addresses to send to.
     *
     * @var array
     */
    private $recipients;

    /**
     * Subject of the email.
     *
     * @var string
     */
    private $subject;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Twig instance.
     *
     * @var EngineInterface
     */
    private $twig;

    /**
     * Mail sender.
     *
     * @var MailerInterface
     */
    private $mailer;

    /**
     * The service that generated the comment.
     *
     * @var CommentService
     */
    private $service;

    public function __construct($sender, $recipients, $subject, LoggerInterface $logger, Environment $templating, MailerInterface $mailer, CommentService $service) {
        $this->sender = $sender;
        $this->recipients = false;
        if (is_array($recipients)) {
            $this->recipients = $recipients;
        } else {
            if ($recipients) {
                $this->recipients = [$recipients];
            }
        }
        $this->subject = $subject;
        $this->logger = $logger;
        $this->twig = $templating;
        $this->mailer = $mailer;
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     *
     * @return NotifierService
     */
    public function setSender($sender) {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return array
     */
    public function getRecipients() {
        return $this->recipients;
    }

    /**
     * @param mixed $recipients
     *
     * @return NotifierService
     */
    public function setRecipients($recipients) {
        if (is_array($recipients)) {
            $this->recipients = $recipients;
        } else {
            if ($recipients) {
                $this->recipients = [$recipients];
            } else {
                $this->recipients = false;
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     *
     * @return NotifierService
     */
    public function setSubject($subject) {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return EngineInterface
     */
    public function getTwig() {
        return $this->twig;
    }

    /**
     * @return NotifierService
     */
    public function setTwig(Environment $twig) {
        $this->twig = $twig;

        return $this;
    }

    /**
     * @return MailerInterface
     */
    public function getMailer() {
        return $this->mailer;
    }

    /**
     * @return NotifierService
     */
    public function setMailer(MailerInterface $mailer) {
        $this->mailer = $mailer;

        return $this;
    }

    /**
     * @return CommentService
     */
    public function getService() {
        return $this->service;
    }

    /**
     * @return NotifierService
     */
    public function setService(CommentService $service) {
        $this->service = $service;

        return $this;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function newComment(Comment $comment) : ?TemplatedEmail {
        if ( ! $this->sender || ! $this->recipients) {
            return null;
        }
        $email = new TemplatedEmail();
        $email->from($this->sender);
        $email->to('dhil@sfu.ca');

        foreach ($this->recipients as $recipient) {
            $email->addBcc($recipient);
        }
        $email->subject('A new comment has been received');
        $email->htmlTemplate('@NinesFeedback/notification/comment.html.twig');
        $email->context([
            'comment' => $comment,
            'service' => $this->service,
        ]);
        $this->mailer->send($email);

        return $email;
    }
}
