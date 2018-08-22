<?php

namespace Nines\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PostStatusRepository
 */
class PostStatusRepository extends EntityRepository {

    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere("e.label LIKE :q");
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");
        return $qb->getQuery()->execute();
    }

}
