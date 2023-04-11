<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Nines\BlogBundle\Entity\PostCategory;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|PostCategory find($id, $lockMode = null, $lockVersion = null)
 * @method PostCategory[] findAll()
 * @method PostCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|PostCategory findOneBy(array $criteria, array $orderBy = null)
 */
class PostCategoryRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, PostCategory::class);
    }
}
