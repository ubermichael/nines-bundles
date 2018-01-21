<?php

namespace Nines\UtilBundle\Tests\Util;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class BaseTestCase extends WebTestCase {

    /**
     * @var ReferenceRepository 
     */
    protected $references;
    
    protected function getFixtures() {
        return array();
    }
    
    protected function setUp() {
        parent::setUp();
        self::bootKernel();
        $this->references = $this->loadFixtures($this->getFixtures())->getReferenceRepository();
    }
    
    /**
     * @return EntityManager
     */
    protected function getDoctrine() {
        static $em = null;
        if( ! $em) {
            $em = static::$kernel->getContainer()->get('doctrine')->getManager();
            $em->getConnection()->getConfiguration()->setSQLLogger(null);
        }
        return $em;
    }
    
    protected function getReference($name) {
        if($this->references->hasReference($name)) {
            return $this->references->getReference($name);
        }
        return null;
    }
}
