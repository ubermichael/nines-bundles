<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\DublinCoreBundle\Entity\Value;

/**
 * @method null|Value find($id, $lockMode = null, $lockVersion = null)
 * @method Value[] findAll()
 * @method Value[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Value findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Value>
 */
class ValueRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Value::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('value')
            ->orderBy('value.id')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('value');
        $qb->addSelect('MATCH (value.data) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
