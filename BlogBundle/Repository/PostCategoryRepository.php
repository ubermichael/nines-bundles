<?php

namespace Nines\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Entity\PostCategory;

/**
 * PostCategoryRepository
 */
class PostCategoryRepository extends EntityRepository
{

    public function typeaheadQuery($q)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere("e.label LIKE :q");
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");
        return $qb->getQuery()->execute();
    }

    public function getPosts(PostCategory $postCategory, $isUser = false)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e');
        $qb->from(Post::class, 'e');

        $qb->where('e.category = :category');

        if( ! $isUser) {
            $qb->innerJoin('e.status', 's');
            $qb->andWhere('s.public = true');
        }
        $qb->orderBy('e.id', 'ASC');
        $qb->setParameter('category', $postCategory);
        return $qb->getQuery();
    }

}
