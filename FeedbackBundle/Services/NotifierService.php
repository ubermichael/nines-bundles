<?php

namespace Nines\FeedbackBundle\Services;

use Nines\FeedbackBundle\Entity\Comment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Templating\EngineInterface;

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
    private $recipient;

    /**
     * Subject of the email.
     *
     * @var string
     */
    private $subject;

    /**
     * Twig instance.
     *
     * @var EngineInterface
     */
    private $templating;

    /**
     * Mail sender.
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * The service that generated the comment.
     *
     * @var CommentService
     */
    private $service;

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $sender
     * @return NotifierService
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return array
     */
    public function getRecipient(): array
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     * @return NotifierService
     */
    public function setRecipient($recipient): NotifierService
    {
        if(is_array($recipient)) {
            $this->recipient = $recipient;
        } else if ($recipient) {
            $this->recipient = array($recipient);
        } else {
            $this->recipient = false;
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     * @return NotifierService
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplating(): EngineInterface
    {
        return $this->templating;
    }

    /**
     * @param EngineInterface $templating
     * @return NotifierService
     */
    public function setTemplating(EngineInterface $templating): NotifierService
    {
        $this->templating = $templating;
        return $this;
    }

    /**
     * @return Swift_Mailer
     */
    public function getMailer(): Swift_Mailer
    {
        return $this->mailer;
    }

    /**
     * @param Swift_Mailer $mailer
     * @return NotifierService
     */
    public function setMailer(Swift_Mailer $mailer): NotifierService
    {
        $this->mailer = $mailer;
        return $this;
    }

    /**
     * @return CommentService
     */
    public function getService(): CommentService
    {
        return $this->service;
    }

    /**
     * @param CommentService $service
     * @return NotifierService
     */
    public function setService(CommentService $service): NotifierService
    {
        $this->service = $service;
        return $this;
    }

    public function __construct($sender, $recipient, $subject, EngineInterface $templating, Swift_Mailer $mailer, CommentService $service) {
        $this->sender = $sender;
        $this->recipient = false;
        if(is_array($recipient)) {
            $this->recipient = $recipient;
        } else if ($recipient) {
            $this->recipient = array($recipient);
        }
        $this->subject = $subject;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->service = $service;
    }

    public function newComment(Comment $comment) {
        if( ! $this->sender || ! $this->recipient) {
            return;
        }
        foreach($this->recipient as $recipient) {
            $message = new Swift_Message();
            $message->setSubject($this->subject);
            $message->setTo($recipient);
            $message->setSender($this->sender);
            $message->setBody($this->templating->render('NinesFeedbackBundle:notification:comment.txt.twig', array(
                'comment' => $comment,
                'service' => $this->service,
            )));
            $this->mailer->send($message);
        }
    }

}
