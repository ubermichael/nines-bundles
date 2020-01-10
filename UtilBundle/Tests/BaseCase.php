<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseCase extends WebTestCase {
    use FixturesTrait;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ReferenceRepository
     */
    protected $references;

    protected function fixtures() : array {
        return [];
    }

    protected function getReference(string $id) {
        if ($this->references->hasReference($id)) {
            return $this->references->getReference($id);
        }
    }

    protected function setUp() : void {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = self::$container->get('doctrine.orm.default_entity_manager');
        $this->references = $this->loadFixtures($this->fixtures())->getReferenceRepository();
    }

    protected function tearDown() : void {
        if ($this->entityManager) {
            $this->entityManager->clear();
            $this->entityManager->close();
            $this->entityManager = null;
        }
        parent::tearDown();
    }
}
