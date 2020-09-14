<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Nines\MediaBundle\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Link find($id, $lockMode = null, $lockVersion = null)
 * @method null|Link findOneBy(array $criteria, array $orderBy = null)
 * @method Link[]    findAll()
 * @method Link[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LinkRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Link::class);
    }

    /**
     * @return Query
     */
    public function indexQuery() {
        return $this->createQueryBuilder('link')
            ->orderBy('link.id')
            ->getQuery()
        ;
    }

    public function searchQuery($q) {
        return $this->createQueryBuilder('link')
            ->addSelect('MATCH(link.url, link.text) AGAINST (:q) AS HIDDEN match')
            ->andHaving('match > 0')
            ->orderBy('match', 'DESC')
            ->setParameter('q', $q)
            ->getQuery()
        ;
    }
}
