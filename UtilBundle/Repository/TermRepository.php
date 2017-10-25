<?php

namespace Nines\UtilBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * TermRepository
 */
abstract class TermRepository extends EntityRepository {
    
    /**
     * Do a typeahead-style query and return the results.
     * 
     * @param string $q
     * @return Collection|Term[]
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('v');
        $qb->where('v.label like :q');
        $qb->setParameter('q', '%' . $q . '%');
        return $qb->getQuery()->execute();
    }
    
}
