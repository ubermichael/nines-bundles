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
use Nines\MediaBundle\Entity\Image;

/**
 * @method null|Image find($id, $lockMode = null, $lockVersion = null)
 * @method Image[] findAll()
 * @method Image[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Image findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Image>
 */
class ImageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Image::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('image')
            ->orderBy('image.originalName')
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
