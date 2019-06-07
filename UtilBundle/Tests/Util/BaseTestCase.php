<?php

namespace Nines\UtilBundle\Tests\Util;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseTestCase extends WebTestCase {

    /**
     * @var ReferenceRepository
     */
    protected $references;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ContainerInterface
     */
    protected $container;

    protected function getFixtures() {
        return null;
    }

    protected function setUp() : void {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();

        $this->em = $this->container->get('doctrine')->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $fixtures = $this->getFixtures();
        if ($fixtures !== null) {
            $this->references = $this->loadFixtures($fixtures)->getReferenceRepository();
        }

        // KnpPaginatorBundle does something odd with GET parameters. Sigh.
        unset($_GET['sort']);
    }

    protected function makeClient($authentication = false, array $params = []): Client {
        $client =  parent::makeClient($authentication, $params);
        $client->disableReboot();
        return $client;
    }

    /**
     * @return EntityManager
     */
    protected function getDoctrine() {
        return $this->em;
    }

    protected function getReference($name) {
        if ($this->references && $this->references->hasReference($name)) {
            return $this->references->getReference($name);
        }
        return null;
    }

    protected function assertNoCookies($client) {
        $this->assertCookieCount($client, 0);
    }

    protected function assertCookieCount($client, $count = 0) {
        $jar = $client->getCookieJar();
        $this->assertEquals($count, count($jar->all()));
        foreach($jar->all() as $cookie) {
            $this->assertEquals('localhost', $cookie->getDomain());
        }
    }

    protected function tearDown(): void {
        parent::tearDown();

        if ($this->em) {
            $this->em->clear();
            $this->em->close();
            $this->em = null; // avoid memory leaks
        }
        if ($this->references) {
            $this->references = null;
        }

        gc_collect_cycles();
    }

}
