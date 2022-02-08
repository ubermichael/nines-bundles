<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\MediaBundle\Entity\Pdf;

/**
 * @method null|Pdf find($id, $lockMode = null, $lockVersion = null)
 * @method Pdf[] findAll()
 * @method Pdf[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Pdf findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Pdf>
 */
class PdfRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Pdf::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('pdf')
            ->orderBy('pdf.originalName')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('e');
        $qb->addSelect('MATCH (e.originalName, e.description) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->orderBy('score', 'desc');
        $qb->setParameter('q', $q);
        $qb->andHaving('score > 0');

        return $qb->getQuery();
    }
}
