<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * TermRepository.
 *
 * @phpstan-extends ServiceEntityRepository<AbstractTerm>
 */
abstract class TermRepository extends ServiceEntityRepository {
    public function indexQuery() : Query {
        return $this->createQueryBuilder('v')
            ->orderBy('v.label')
            ->getQuery();
    }

    public function typeaheadQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('v');
        $qb->where('v.label like :q');
        $qb->setParameter('q', '%' . $q . '%');
        $qb->orderBy('v.label');

        return $qb->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('v');
        $qb->where('MATCH(v.label, v.description) AGAINST (:q BOOLEAN) > 0.0');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
