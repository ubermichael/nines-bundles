<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Services;

use Nines\UtilBundle\Entity\Link;
use Nines\UtilBundle\Entity\LinkableInterface;
use Nines\UtilBundle\Repository\LinkRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nines\UtilBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Commenting service for Symfony.
 */
class LinkManager implements EventSubscriber {
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * Mapping of class name to route name.
     *
     * @var array
     */
    private $routing;

    /**
     * @var LinkRepository
     */
    private $linkRepository;

    /**
     * Build the commenting service.
     */
    public function __construct(array $routing) {
        $this->routing = $routing;
    }

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     */
    public function setLogger(LoggerInterface $logger) : void {
        $this->logger = $logger;
    }

    /**
     * @required
     */
    public function setRouter(UrlGeneratorInterface $router) : void {
        $this->router = $router;
    }

    /**
     * @required
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
     * Find the entity corresponding to a comment.
     *
     * @return mixed
     */
    public function findEntity(Link $link) {
        list($class, $id) = explode(':', $link->getEntity());
        if ($this->em->getMetadataFactory()->isTransient($class)) {
            return null;
        }

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a link refers to.
     */
    public function entityType(Link $link) : ?string {
        $entity = $this->findEntity($link);
        if ( ! $entity) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($entity);
        } catch (ReflectionException $e) {
            $this->logger->error('Cannot find entity for link ' . $link->getEntity());

            return null;
        }

        return $reflection->getShortName();
    }

    /**
     * Find the links for an entity.
     *
     * @param mixed $entity
     *
     * @return Collection|Link[]
     */
    public function findLinks($entity) {
        $class = ClassUtils::getClass($entity);

        return $this->linkRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    public function setLinks(LinkableInterface $entity, $links) : void {
        foreach ($entity->getLinks() as $link) {
            if ($this->em->contains($link)) {
                $this->em->remove($link);
            }
        }
        $entity->setLinks($links);
        foreach($links as $link) {
            $this->em->persist($link);
        }
    }

    public function linkToEntity($link) {
        list($class, $id) = explode(':', $link->getEntity());

        return $this->router->generate($this->routing[$class], ['id' => $id]);
    }

    public function getSubscribedEvents() {
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
