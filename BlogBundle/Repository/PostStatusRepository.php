<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nines\BlogBundle\Entity\PostStatus;

/**
 * PostStatusRepository.
 */
class PostStatusRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PostStatus::class);
    }

    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.label LIKE :q');
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
