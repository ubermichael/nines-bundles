<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * PostStatusRepository.
 */
class PostStatusRepository extends EntityRepository {
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.label LIKE :q');
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
