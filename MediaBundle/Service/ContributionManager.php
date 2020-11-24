<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use DateTimeImmutable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nines\MediaBundle\Entity\ContributorInterface;
use Nines\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Security;

/**
 * Commenting service for Symfony.
 */
class ContributionManager implements EventSubscriber {
    /**
     * @var Security
     */
    private $security;

    public function addContributor($entity) : void {
        if ( ! $entity instanceof ContributorInterface) {
            return;
        }
        /** @var User $user */
        $user = $this->security->getUser();
        if ( ! $user) {
            return;
        }
        $entity->addContribution(new DateTimeImmutable(), $user->getFullname());
    }

    /**
     * @required
     */
    public function setSecurity(Security $security) : void {
        $this->security = $security;
    }

    public function getSubscribedEvents() {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    public function preUpdate(LifecycleEventArgs $args) : void {
        $this->addContributor($args->getEntity());
    }

    public function prePersist(LifecycleEventArgs $args) : void {
        $this->addContributor($args->getEntity());
    }
}
