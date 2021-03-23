<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\FeedbackBundle\Entity\CommentNote;

/**
 * Comment Note Repository.
 */
class CommentNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, CommentNote::class);
    }

    /**
     * Prepare a full-text search query and return it.
     *
     * @param string $q
     *
     * @return Query
     *
     * @todo Add a $private parameter to include private/unpublished comments.
     */
    public function fulltextQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect("MATCH_AGAINST (e.content, :q 'IN BOOLEAN MODE') as score");
        $qb->add('where', "MATCH_AGAINST (e.content, :q 'IN BOOLEAN MODE') > 0.5");
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
