<?php

declare(strict_types=1);

/*
 * (c) 2023 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @template T of AbstractEntity
 *
 * @template-extends ServiceEntityRepository<T>
 */
abstract class AbstractEntityRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry, string $className) {
        parent::__construct($registry, $className);
    }

    public function indexQuery() : Query {
        $qb = $this->createQueryBuilder('entity');
        $qb->addOrderBy('entity.id', 'ASC');

        return $qb->getQuery();
    }

    public function save(AbstractEntity $entity, bool $flush = false) : void {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AbstractEntity $entity, bool $flush = false) : void {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush() : void {
        $this->getEntityManager()->flush();
    }
}
