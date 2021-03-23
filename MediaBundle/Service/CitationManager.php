<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Nines\MediaBundle\Entity\Citation;
use Nines\MediaBundle\Entity\CitationInterface;
use Nines\MediaBundle\Repository\CitationRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Commenting service for Symfony.
 */
class CitationManager implements EventSubscriber
{
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
     * @var CitationRepository
     */
    private $citationRepository;

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
    public function setCitationRepository(CitationRepository $citationRepository) : void {
        $this->citationRepository = $citationRepository;
    }

    /**
     * Check if an entity is configured to accept citations.
     */
    public function acceptsCitations(AbstractEntity $entity) : bool {
        return $entity instanceof CitationInterface;
    }

    /**
     * Find the entity corresponding to a comment.
     *
     * @return mixed
     */
    public function findEntity(Citation $citation) {
        list($class, $id) = explode(':', $citation->getEntity());

        return $this->em->getRepository($class)->find($id);
    }

    /**
     * Return the short class name for the entity a citation refers to.
     */
    public function entityType(Citation $citation) : ?string {
        $entity = $this->findEntity($citation);
        if ( ! $entity) {
            return null;
        }

        try {
            $reflection = new ReflectionClass($entity);
        } catch (ReflectionException $e) {
            $this->logger->error('Cannot find entity for citation ' . $citation->getEntity());

            return null;
        }

        return $reflection->getShortName();
    }

    /**
     * Find the citations for an entity.
     *
     * @param mixed $entity
     *
     * @return Citation[]|Collection
     */
    public function findCitations($entity) {
        $class = get_class($entity);

        return $this->citationRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    /**
     * Add a citation to an entity.
     *
     * @param mixed $entity
     *
     * @throws Exception
     */
    public function addCitation(CitationInterface $entity, Citation $citation) : Citation {
        $citation->setEntity($entity);
        $this->em->persist($citation);

        return $citation;
    }

    public function setCitations(CitationInterface $entity, $citations) : void {
        foreach ($entity->getCitations() as $citation) {
            $this->em->remove($citation);
        }

        foreach ($citations as $citation) {
            $entity->addCitation($citation);
            $this->em->persist($citation);
        }
    }

    public function linkToEntity($citation) {
        list($class, $id) = explode(':', $citation->getEntity());

        if ( ! isset($this->routing[$class])) {
            $this->logger->error('No routing information for ' . $class);

            return;
        }

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
        if ( ! $entity instanceof CitationInterface) {
            return;
        }
        $entity->setCitations($this->findCitations($entity));
    }

    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof CitationInterface) {
            return;
        }

        foreach ($entity->getCitations() as $citation) {
            $this->em->remove($citation);
        }
    }
}
