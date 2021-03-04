<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nines\MediaBundle\Entity\Citation;

/**
 * @method null|Citation find($id, $lockMode = null, $lockVersion = null)
 * @method null|Citation findOneBy(array $criteria, array $orderBy = null)
 * @method Citation[]    findAll()
 * @method Citation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CitationRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Citation::class);
    }

    public function indexQuery() {
        return $this->createQueryBuilder('Citation')
            ->orderBy('Citation.id')
            ->getQuery()
        ;
    }

    public function searchQuery(string $q) {
        $qb = $this->createQueryBuilder('Citation');
        $qb->addSelect('MATCH (citation.citation, citation.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
