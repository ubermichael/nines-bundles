<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\FeedbackBundle\Entity\CommentNote;

/**
 * @method null|CommentNote find($id, $lockMode = null, $lockVersion = null)
 * @method CommentNote[] findAll()
 * @method CommentNote[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|CommentNote findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<\Nines\FeedbackBundle\Entity\CommentNote>
 */
class CommentNoteRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, CommentNote::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('commentNote')
            ->orderBy('commentNote.id')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('commentNote');
        $qb->addSelect('MATCH (commentNote.content) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
