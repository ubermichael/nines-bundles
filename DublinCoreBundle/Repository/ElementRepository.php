<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|Element find($id, $lockMode = null, $lockVersion = null)
 * @method Element[] findAll()
 * @method Element[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Element findOneBy(array $criteria, array $orderBy = null)
 */
class ElementRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Element::class);
    }
}
