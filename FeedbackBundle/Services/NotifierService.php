<?php

namespace Nines\FeedbackBundle\Services;

use Nines\FeedbackBundle\Entity\Comment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Templating\EngineInterface;

class NotifierService {
    
    private $sender;

    private $recipient;

    private $subject;

    private $templating;

    private $mailer;

    private $service;

    public function __construct($sender, $recipient, $subject, EngineInterface $templating, Swift_Mailer $mailer, CommentService $service) {
        $this->sender = $sender;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->templating = $templating;
        $this->mailer = $mailer;
        $this->service = $service;
    }

    public function newComment(Comment $comment) {
        if( ! $this->sender || ! $this->recipient) {
            return;
        }
        $message = new Swift_Message();
        $message->setSubject($this->subject);
        $message->setTo($this->recipient);
        $message->setSender($this->sender);
        $message->setBody($this->templating->render('NinesFeedbackBundle:notification:comment.txt.twig', array(
            'comment' => $comment,
            'service' => $this->service,
        )));
        $this->mailer->send($message);
    }

}
