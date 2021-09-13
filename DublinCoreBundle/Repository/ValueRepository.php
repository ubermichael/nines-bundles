<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\DublinCoreBundle\Entity\Value;

/**
 * @method null|Value find($id, $lockMode = null, $lockVersion = null)
 * @method null|Value findOneBy(array $criteria, array $orderBy = null)
 * @method Value[] findAll()
 * @method Value[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ValueRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Value::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('value')
            ->orderBy('value.id')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Value[]
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('value');
        $qb->addSelect('MATCH(value.data) AGAINST (:q BOOLEAN) AS HIDDEN score');
        $qb->andWhere('MATCH(value.data) AGAINST (:q BOOLEAN) > 0');
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
