<?php

namespace Nines\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PostCategoryRepository
 */
class PostCategoryRepository extends EntityRepository {

    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere("e.label LIKE :q");
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");
        return $qb->getQuery()->execute();
    }

}
