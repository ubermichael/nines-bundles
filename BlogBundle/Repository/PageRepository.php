<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Nines\BlogBundle\Entity\Page;

/**
 * @method null|Page find($id, $lockMode = null, $lockVersion = null)
 * @method Page[] findAll()
 * @method Page[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Page findOneBy(array $criteria, array $orderBy = null)
 * @phpstan-extends ServiceEntityRepository<\Nines\BlogBundle\Entity\Page>
 */
class PageRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Page::class);
    }

    public function indexQuery(?bool $includePrivate = false) : Query {
        $qb = $this->createQueryBuilder('page');
        if ( ! $includePrivate) {
            $qb->andWhere('page.public = true');
        }
        $qb->orderBy('page.weight', 'asc');
        $qb->addOrderBy('page.title');

        return $qb->getQuery();
    }

    public function findHomepage() : ?Page {
        return $this->findOneBy(['homepage' => 1]);
    }

    public function clearHomepages(Page $page) : void {
        $qb = $this->createQueryBuilder('e');
        $qb->update(Page::class, 'e');
        $qb->set('e.homepage', 0);
        $qb->where('e.id <> :id');
        $qb->setParameter('id', $page->getId());
        $qb->getQuery()->execute();
    }

    public function searchQuery(string $q, ?bool $private = false) : Query {
        $qb = $this->createQueryBuilder('page');
        $qb->addSelect('MATCH (page.title, page.searchable) AGAINST(:q BOOLEAN) as HIDDEN score');
        if ( ! $private) {
            $qb->andWhere('page.public = true');
        }
        $qb->andHaving('score > 0');
        $qb->orderBy('score', 'DESC');
        $qb->setParameter('q', $q);

        return $qb->getQuery();
    }
}
