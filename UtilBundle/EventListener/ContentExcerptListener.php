<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Nines\UtilBundle\Entity\ContentEntityInterface;
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

    private function generateExcerpt($entity) : void {
        if ( ! $entity instanceof ContentEntityInterface) {
            return;
        }
        if ($entity->getExcerpt()) {
            return;
        }
        $content = $entity->getContent();
        $plain = $this->text->plain($content);
        $entity->setExcerpt($this->text->trim($plain));
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $this->generateExcerpt($args->getEntity());
    }

    public function preUpdate(PreUpdateEventArgs $args) : void {
        $this->generateExcerpt($args->getEntity());
    }
}
