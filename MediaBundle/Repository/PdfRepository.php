<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\MediaBundle\Entity\Pdf;

/**
 * @method null|Pdf find($id, $lockMode = null, $lockVersion = null)
 * @method null|Pdf findOneBy(array $criteria, array $orderBy = null)
 * @method Pdf[] findAll()
 * @method Pdf[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PdfRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Pdf::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('pdf')
            ->orderBy('pdf.originalName')
            ->getQuery()
            ;
    }

    /**
     * @param string $q
     *
     * @return Collection|Pdf[]
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('pdf');
        $qb->andWhere('pdf.originalName LIKE :q');
        $qb->orderBy('pdf.originalName', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    public function searchQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('MATCH (e.originalName, e.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', $q);
        $qb->andHaving('score > 0');

        return $qb->getQuery();
    }
}
