<?php

namespace Nines\FeedbackBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * Comment Repository.
 */
class CommentRepository extends EntityRepository
{
    /**
     * Prepare a full-text search query and return it.
     * 
     * @todo Add a $private parameter to include private/unpublished comments.
     * 
     * @param string $q
     * @return Query
     */
    public function fulltextQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect("MATCH_AGAINST (e.fullname, e.content, :q 'IN BOOLEAN MODE') as score");
        $qb->add('where', "MATCH_AGAINST (e.fullname, e.content, :q 'IN BOOLEAN MODE') > 0.5");
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', $q);
        return $qb->getQuery();
    }
}
