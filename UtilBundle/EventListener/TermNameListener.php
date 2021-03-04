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
use Nines\UtilBundle\Entity\AbstractTerm;
use Nines\UtilBundle\Services\Text;
use Psr\Log\LoggerInterface;

class TermNameListener {
    /**
     * @var Text
     */
    private $text;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Text $text, LoggerInterface $logger) {
        $this->text = $text;
        $this->logger = $logger;
    }

    /**
     * Generate a slug for an Abstract Term, based on the entity's label.
     *
     * @param $entity
     */
    private function generateSlug($entity) : void {
        if ( ! $entity instanceof AbstractTerm) {
            return;
        }
        if ($entity->getName()) {
            return;
        }
        $label = $entity->getLabel();
        $slug = $this->text->slug($label);
        $entity->setName($slug);
    }

    /**
     * Automatically generate a slug for an AbstractTerm.
     */
    public function prePersist(LifecycleEventArgs $args) : void {
        $this->generateSlug($args->getEntity());
    }

    /**
     * Automatically update a slug for an AbstractTerm.
     */
    public function preUpdate(PreUpdateEventArgs $args) : void {
        $this->generateSlug($args->getEntity());
    }
}
