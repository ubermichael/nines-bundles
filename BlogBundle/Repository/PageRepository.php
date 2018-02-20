<?php

namespace Nines\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nines\BlogBundle\Entity\PostStatus;

/**
 * PageRepository
 */
class PageRepository extends EntityRepository
{
    /**
     * Get a query to list pages ordered by weight and respecting private pages.
     * 
     * @param bool $private
     * @return Query
     */
    public function listQuery($private = false) {
        $qb = $this->createQueryBuilder('e');
        if( ! $private ) {
            $qb->andWhere('e.public = true');
        }
        $qb->orderBy('e.weight', 'asc');
        return $qb->getQuery();
    }
    
    /**
     * Get a full text query of pages.
     * 
     * @param string $q
     * @param bool $private
     * @return Query
     */
    public function fulltextQuery($q, $private = false) {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect("MATCH (e.title, e.searchable) AGAINST (:q BOOLEAN) as HIDDEN score");
        $qb->andWhere("MATCH (e.title, e.searchable) AGAINST (:q BOOLEAN) > 0");
        if( ! $private) {
            $qb->andWhere('e.public = true');
        }
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', $q);
        return $qb->getQuery();
    }
    
}
