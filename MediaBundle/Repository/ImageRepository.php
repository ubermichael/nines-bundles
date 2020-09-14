<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Repository;

use Nines\MediaBundle\Entity\Image;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Image find($id, $lockMode = null, $lockVersion = null)
 * @method null|Image findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends AbstractImageRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Image::class);
    }
}
