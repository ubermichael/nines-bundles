<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Services;

use Nines\UtilBundle\Entity\Reference;
use Nines\UtilBundle\Entity\ReferenceableInterface;
use Nines\UtilBundle\Repository\ReferenceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Nines\UtilBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Commenting service for Symfony.
 */
class ReferenceManager implements EventSubscriber {
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
     * @var ReferenceRepository
     */
    private $referenceRepository;

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
    public function setReferenceRepository(ReferenceRepository $referenceRepository) : void {
        $this->referenceRepository = $referenceRepository;
    }

    /**
     * Check if an entity is configured to accept references.
     */
    public function acceptsReferences(AbstractEntity $entity) : bool {
        return $entity instanceof ReferenceableInterface;
    }

    /**
     * Find the entity corresponding to a comment.
     *
     * @return mixed
     */
    public function findEntity(Reference $reference) {
        list($class, $id) = explode(':', $reference->getEntity());

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a reference refers to.
     */
    public function entityType(Reference $reference) : ?string {
        $entity = $this->findEntity($reference);
        if ( ! $entity) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($entity);
        } catch (ReflectionException $e) {
            $this->logger->error('Cannot find entity for reference ' . $reference->getEntity());

            return null;
        }

        return $reflection->getShortName();
    }

    /**
     * Find the references for an entity.
     *
     * @param mixed $entity
     *
     * @return Collection|Reference[]
     */
    public function findReferences($entity) {
        $class = get_class($entity);

        return $this->referenceRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    /**
     * Add a reference to an entity.
     *
     * @param mixed $entity
     *
     * @throws Exception
     */
    public function addReference(ReferenceableInterface $entity, Reference $reference) : Reference {
        $reference->setEntity($entity);
        $this->em->persist($reference);

        return $reference;
    }

    public function setReferences(ReferenceableInterface $entity, $references) : void {
        foreach ($entity->getReferences() as $reference) {
            $this->em->remove($reference);
        }

        foreach ($references as $reference) {
            $entity->addReference($reference);
            $this->em->persist($reference);
        }
    }

    public function linkToEntity($citation) {
        list($class, $id) = explode(':', $citation->getEntity());

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
        if ( ! $entity instanceof ReferenceableInterface) {
            return;
        }
        $entity->setReferences($this->findReferences($entity));
    }

    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof ReferenceableInterface) {
            return;
        }

        foreach ($entity->getReferences() as $reference) {
            $this->em->remove($reference);
        }
    }
}
