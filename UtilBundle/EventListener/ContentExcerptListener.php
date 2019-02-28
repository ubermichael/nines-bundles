<?php

namespace Nines\UtilBundle\EventListener;

use Nines\UtilBundle\Entity\ContentEntityInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Nines\UtilBundle\Services\Text;
use Psr\Log\LoggerInterface;

class ContentExcerptListener {

    /**
     * @var Text
     */
    private $text;

    private $logger;

    public function __construct(Text $text, LoggerInterface $logger) {
        $this->text = $text;
        $this->logger = $logger;
    }

    public function prePersist(LifecycleEventArgs $args) {
        $this->generateExcerpt($args->getEntity());
    }

    public function preUpdate(PreUpdateEventArgs $args) {
        $this->generateExcerpt($args->getEntity());
    }

    private function generateExcerpt($entity) {
        if (!$entity instanceof ContentEntityInterface) {
            return;
        }
        if($entity->getExcerpt()) {
            return;
        }
        $content = $entity->getContent();
        $plain = $this->text->plain($content);
        $entity->setExcerpt($this->text->trim($plain));
    }

}
