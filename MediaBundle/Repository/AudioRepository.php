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
use Nines\MediaBundle\Entity\Audio;

/**
 * @method null|Audio find($id, $lockMode = null, $lockVersion = null)
 * @method Audio[] findAll()
 * @method Audio[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Audio findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<Audio>
 */
class AudioRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Audio::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('audio')
            ->orderBy('audio.originalName')
            ->getQuery();
    }

    public function searchQuery(string $q) : Query {
        $qb = $this->createQueryBuilder('audio');
        $qb->andWhere('audio.originalName LIKE :q');
        $qb->orderBy('audio.originalName', 'ASC');
        $qb->setParameter('q', "%{$q}%");

        return $qb->getQuery();
    }
}
