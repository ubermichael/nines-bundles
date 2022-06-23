<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Nines\DublinCoreBundle\Entity\Value;
use Nines\DublinCoreBundle\Entity\ValueInterface;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Link management service for Symfony.
 */
class ValueManager implements EventSubscriber {
    private ?ValueRepository $valueRepository = null;

    private ?EntityManagerInterface $em = null;

    /**
     * Find the values for an entity.
     *
     * @param AbstractEntity|ValueInterface $entity
     *
     * @return Value[]
     */
    public function findValues($entity) : array {
        $class = ClassUtils::getClass($entity);

        return $this->valueRepository->findBy([
            'entity' => $class . ':' . $entity->getId(),
        ]);
    }

    /**
     * @param Collection<int,Value>|Value[] $values
     *
     * @throws Exception
     */
    public function setValues(AbstractEntity $entity, $values) : void {
        if ( ! $entity instanceof ValueInterface) {
            throw new Exception(get_class($entity) . ' does not implement ValueInterface.');
        }
        foreach ($this->findValues($entity) as $value) {
            $this->em->remove($value);
        }
        $entity->setValues($values);
        foreach ($values as $value) {
            $this->em->persist($value);
        }
    }

    public function getSubscribedEvents() : array {
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

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setValueRepository(ValueRepository $valueRepository) : void {
        $this->valueRepository = $valueRepository;
    }
}
