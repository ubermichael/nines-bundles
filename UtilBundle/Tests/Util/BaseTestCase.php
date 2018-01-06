<?php

namespace Nines\UtilBundle\Tests\Util;

use Closure;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use ReflectionObject;

abstract class BaseTestCase extends WebTestCase {

    /**
     * @var EntityManager
     */
    protected $em;
    
    /**
     * @var ReferenceRepository 
     */
    protected $references;
    
    /**
     * http://stackoverflow.com/a/32879462/9316
     *
     * @var Closure
     */
    private static $kernelModifier = null;
    
    protected function getFixtures() {
        return array();
    }
    
    protected function setUp() {
        parent::setUp();
        self::bootKernel();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->references = $this->loadFixtures($this->getFixtures())->getReferenceRepository();
    }
    
    protected function getReference($name) {
        if($this->references->hasReference($name)) {
            return $this->references->getReference($name);
        }
        return null;
    }

    protected static function createClient(array $options = [], array $server = []) {
        static::bootKernel($options);
        if (self::$kernelModifier !== null) {
            self::$kernelModifier->__invoke();
            self::$kernelModifier = null;
        }
        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);
        return $client;
    }

    public function setKernelModifier(Closure $kernelModifier) {
        self::$kernelModifier = $kernelModifier;
        $this->ensureKernelShutdown();
    }

    protected function tearDown() {        
        parent::tearDown();
//        $this->em->close();
//        $this->em = null;
        
        $refl = new ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
        static::$kernel->shutdown();
        gc_collect_cycles();
    }

}
