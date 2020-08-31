<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * TermRepository.
 */
abstract class TermRepository extends ServiceEntityRepository {
    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('v')
            ->orderBy('v.label')
            ->getQuery()
            ;
    }

    /**
     * Do a typeahead-style query and return the results.
     *
     * @param string $q
     *
     * @return AbstractTerm[]|Collection
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('v');
        $qb->where('v.label like :q');
        $qb->setParameter('q', '%' . $q . '%');

        return $qb->getQuery()->execute();
    }

    /**
     * Create a simple full-text search query on the term label and description.
     *
     * @param string $q
     * @return AbstractTerm[]|Collection
     */
    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('v');
        $qb->where('MATCH(v.label, v.description) AGAINST (:q BOOLEAN) > 0.0');
        $qb->setParameter('q', $q);

        return $qb->getQuery()->execute();
    }
}
