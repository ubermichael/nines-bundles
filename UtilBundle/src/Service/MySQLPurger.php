<?php

declare(strict_types=1);

namespace Nines\UtilBundle\Service;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

class MySQLPurger extends ORMPurger {
    private LoggerInterface $logger;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger) {
        parent::__construct($em, []);
        $this->em = $em;
        $this->logger = $logger;
        $this->setPurgeMode(self::PURGE_MODE_TRUNCATE);
    }

    public function purge() : void {
        $connection = $this->em->getConnection();

        try {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0');
            parent::purge();
            // parent::purge(); causes an implict transaction commit,
            // so start another one here.
            $connection->beginTransaction();
        } catch (Exception $e) {
            $this->logger->error("Error disabling foreign key checks: {$e->getMessage()}");
        } finally {
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
