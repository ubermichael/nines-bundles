<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Nines\DublinCoreBundle\Entity\Value;
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Service\AbstractFileManager;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Link management service for Symfony.
 */
class ValueManager extends AbstractFileManager implements EventSubscriber {
    /**
     * @var ValueRepository
     */
    private $valueRepository;

    /**
     * @required
     */
    public function setValueRepository(ValueRepository $valueRepository) : void {
        $this->valueRepository = $valueRepository;
    }

    /**
     * Check if an entity is configured to accept values.
     */
    public function acceptsValues(AbstractEntity $entity) : bool {
        return $entity instanceof ValueInterface;
    }

    /**
     * Find the values for an entity.
     *
     * @param mixed $entity
     *
     * @return Collection|Value[]
     */
    public function findValues($entity) {
        $class = ClassUtils::getClass($entity);

        return $this->valueRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    public function setValues(ValueInterface $entity, Collection $values) : void {
        foreach ($this->findValues($entity) as $value) {
            $this->em->remove($value);
        }
        $entity->setValues($values);
        foreach ($values as $value) {
            $this->em->persist($value);
        }
    }

    public function getSubscribedEvents() {
        return [
            Events::postLoad,
            Events::preRemove,
        ];
    }

    public function postLoad(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof ValueInterface) {
            return;
        }
        $entity->setValues($this->findValues($entity));
    }

    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getObject();
        if ( ! $entity instanceof ValueInterface) {
            return;
        }

        foreach ($entity->getValues() as $value) {
            $this->em->remove($value);
        }
    }
}
