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
use Nines\MediaBundle\Entity\Link;

/**
 * @method null|Link find($id, $lockMode = null, $lockVersion = null)
 * @method Link[] findAll()
 * @method Link[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Link findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Link>
 */
class LinkRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Link::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('link')
            ->orderBy('link.id')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('link');
        $qb->addSelect('MATCH (link.url, link.text) AGAINST(:q BOOLEAN) as HIDDEN score');
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
