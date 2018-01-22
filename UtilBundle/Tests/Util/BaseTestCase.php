<?php

namespace Nines\UtilBundle\Tests\Util;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Bridge\Monolog\Logger;
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
     * @var Logger
     */
    protected $logger;
    
    /**
     * @var ContainerInterface
     */
    protected $container;
    
    protected function getFixtures() {
        return null;
    }
    
    protected function setUp() {
        parent::setUp();
        $kernel = self::bootKernel();
        $this->container = $kernel->getContainer();
        
        $this->em = $this->container->get('doctrine')->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $this->logger = $this->container->get('logger');
        
        $fixtures = $this->getFixtures();
        if($fixtures !== null) {
            $this->references = $this->loadFixtures($fixtures)->getReferenceRepository();
        }
    }
    
    /**
     * @return EntityManager
     */
    protected function getDoctrine() {
        return $this->em;
    }
    
    protected function getReference($name) {
        if($this->references && $this->references->hasReference($name)) {
            return $this->references->getReference($name);
        }
        return null;
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        $this->em->clear();
        $this->em->close();        
        $this->em = null; // avoid memory leaks'
        
        $this->references = null;
        
        $this->logger->clear();
        $this->logger = null;
                
        gc_collect_cycles();
    }
    
}
