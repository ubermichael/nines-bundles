<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Entity\LinkableInterface;
use Nines\MediaBundle\Repository\LinkRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\AbstractEntityInterface;

/**
 * Link management service for Symfony.
 */
class LinkManager extends AbstractFileManager implements EventSubscriber {
    private ?LinkRepository $linkRepository = null;

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setLinkRepository(LinkRepository $linkRepository) : void {
        $this->linkRepository = $linkRepository;
    }

    /**
     * Check if an entity is configured to accept links.
     */
    public function acceptsLinks(AbstractEntity $entity) : bool {
        return $entity instanceof LinkableInterface;
    }

    /**
     * Find the links for an entity.
     *
     * @return array<Link>
     */
    public function findLinks(AbstractEntityInterface $entity) : array {
        $class = ClassUtils::getClass($entity);

        return $this->linkRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    /**
     * @param array<Link>|Collection<int,Link> $links
     */
    public function setLinks(LinkableInterface $entity, $links) : void {
        foreach ($this->findLinks($entity) as $link) {
            $this->em->remove($link);
        }
        $entity->setLinks($links);
        foreach ($links as $link) {
            $this->em->persist($link);
        }
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents() : array {
        return [
            Events::postLoad,
            Events::preRemove,
        ];
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof LinkableInterface) {
            return;
        }
        $entity->setLinks($this->findLinks($entity));
    }

    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof LinkableInterface) {
            return;
        }

        foreach ($entity->getLinks() as $link) {
            $this->em->remove($link);
        }
    }
}
