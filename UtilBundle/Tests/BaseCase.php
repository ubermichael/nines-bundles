<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\Mapping\MappingException as MappingException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
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

    /**
     * @var array|SplFileInfo[]|string[]
     */
    protected $cleanup;

    /**
     * Get a list of fixture classes to load.
     */
    protected function fixtures() : array {
        return [];
    }

    /**
     * Get one data fixture. If $reload is true, the fixture will
     * be fetched from the database.
     *
     * @param bool $reload
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     *
     * @return null|object
     */
    protected function getReference(string $id, $reload = false) {
        if ( ! $this->references->hasReference($id)) {
            return;
        }
        $object = $this->references->getReference($id);
        if ( ! $reload) {
            return $object;
        }

        return $this->entityManager->find(get_class($object), $object->getId());
    }

    protected function cleanup($files) : void {
        if ( ! is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo) {
                $this->cleanup[] = $file->getRealPath();
            } else {
                if (is_string($file)) {
                    $this->cleanup[] = $file;
                } else {
                    throw new Exception('Cannot clean up ' . get_class($file));
                }
            }
        }
    }

    /**
     * Set up the container and fixtures.
     */
    protected function setUp() : void {
        parent::setUp();
        self::bootKernel();
        $this->entityManager = self::$container->get('doctrine.orm.default_entity_manager');
        $this->references = $this->loadFixtures($this->fixtures())->getReferenceRepository();
        $this->cleanup = [];
    }

    /**
     * Clear out the memory for the next test run.
     *
     * @throws MappingException
     */
    protected function tearDown() : void {
        if ($this->entityManager) {
            $this->entityManager->clear();
            $this->entityManager->close();
            $this->entityManager = null;
        }
        parent::tearDown();

        foreach ($this->cleanup as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
