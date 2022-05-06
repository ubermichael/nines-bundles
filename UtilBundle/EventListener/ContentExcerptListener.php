<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Nines\UtilBundle\Entity\ContentEntityInterface;
use Nines\UtilBundle\Services\Text;

class ContentExcerptListener {
    private ?Text $text = null;

    public function __construct(Text $text) {
        $this->text = $text;
    }

    private function generateExcerpt(ContentEntityInterface $entity) : void {
        if ($entity->getExcerpt()) {
            return;
        }
        $content = $entity->getContent();
        $plain = $this->text->plain($content);
        $entity->setExcerpt($this->text->trim($plain));
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ( ! $entity instanceof ContentEntityInterface) {
            return;
        }
        $this->generateExcerpt($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args) : void {
        $entity = $args->getEntity();
        if ( ! $entity instanceof ContentEntityInterface) {
            return;
        }
        $this->generateExcerpt($entity);
    }
}
