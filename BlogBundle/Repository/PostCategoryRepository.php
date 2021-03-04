<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Entity\PostCategory;

/**
 * PostCategoryRepository.
 */
class PostCategoryRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PostCategory::class);
    }

    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.label LIKE :q');
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    public function getPosts(PostCategory $postCategory, $isUser = false) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e');
        $qb->from(Post::class, 'e');

        $qb->where('e.category = :category');

        if ( ! $isUser) {
            $qb->innerJoin('e.status', 's');
            $qb->andWhere('s.public = true');
        }
        $qb->orderBy('e.id', 'DESC');
        $qb->setParameter('category', $postCategory);

        return $qb->getQuery();
    }
}
