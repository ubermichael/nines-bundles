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
use Nines\FeedbackBundle\Entity\Comment;

/**
 * @method null|Comment find($id, $lockMode = null, $lockVersion = null)
 * @method Comment[] findAll()
 * @method Comment[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Comment findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Comment::class);
    }

    public function indexQuery(?string $status = null) : Query {
        $qb = $this->createQueryBuilder('comment');
        if ($status) {
            $qb->innerJoin('comment.status', 'status');
            $qb->andWhere('status.name = :status');
            $qb->setParameter('status', $status);
        }

        return $qb->orderBy('comment.id')->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('comment');
        $qb->addSelect('MATCH (comment.fullname, comment.content) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
