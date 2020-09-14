<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Nines\MediaBundle\Entity\AbstractImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|AbstractImage find($id, $lockMode = null, $lockVersion = null)
 * @method null|AbstractImage findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractImage[]    findAll()
 * @method AbstractImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractImageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry, $class) {
        parent::__construct($registry, $class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('abstractImage')
            ->orderBy('abstractImage.originalName')
            ->getQuery()
        ;
    }

    /**
     * @param string $q
     *
     * @return AbstractImage[]|Collection
     */
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('abstractImage');
        $qb->andWhere('abstractImage.originalName LIKE :q');
        $qb->orderBy('abstractImage.originalName', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }
}
