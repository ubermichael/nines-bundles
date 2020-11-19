<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Repository;

use Nines\UtilBundle\Entity\Link;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
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

    public function indexQuery() {
        return $this->createQueryBuilder('link')
            ->orderBy('link.id')
            ->getQuery()
        ;
    }

    /**
     * @return Collection|Link[]
     */
    public function typeaheadQuery(string $q) {
        $qb = $this->createQueryBuilder('link');
        $qb->andWhere('link.url LIKE :q');
        $qb->orderBy('link.url', 'ASC');
        $qb->setParameter('q', "{$q}%");

        return $qb->getQuery()->execute();
    }

    public function searchQuery(string $q) {
        $qb = $this->createQueryBuilder('link');
        $qb->addSelect('MATCH (link.url, link.text) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
