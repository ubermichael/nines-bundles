<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Purger;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class ForeignKeyPurger extends ORMPurger {
    private ?EntityManagerInterface $em = null;

    public function __construct(?EntityManagerInterface $em = null, array $excluded = []) {
        parent::__construct($em, $excluded);
        $this->setPurgeMode(self::PURGE_MODE_TRUNCATE);
        $this->em = $em;
    }

    public function purge() : void {
        $connection = $this->em->getConnection();

        try {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
            parent::purge();
        } finally {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
