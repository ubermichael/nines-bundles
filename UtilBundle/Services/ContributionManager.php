<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Services;

use DateTimeImmutable;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Entity\ContributorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Contribution service for Symfony.
 */
class ContributionManager implements EventSubscriber {
    private ?Security $security = null;

    /**
     * @param mixed $entity
     */
    public function addContributor($entity) : void {
        if ( ! $entity instanceof ContributorInterface) {
            return;
        }
        /** @var ?User $user */
        $user = $this->security->getUser();
        if ( ! $user) {
            return;
        }
        $entity->addContribution(new DateTimeImmutable(), $user->getFullname());
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setSecurity(Security $security) : void {
        $this->security = $security;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSubscribedEvents() : array {
        return [
            Events::preUpdate,
            Events::prePersist,
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function preUpdate(LifecycleEventArgs $args) : void {
        $this->addContributor($args->getEntity());
    }

    /**
     * @codeCoverageIgnore
     */
    public function prePersist(LifecycleEventArgs $args) : void {
        $this->addContributor($args->getEntity());
    }
}
