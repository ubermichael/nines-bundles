<?php

namespace AppUserBundle\Command;

use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Nines\UserBundle\Command\CreateUserCommand;
use Nines\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends WebTestCase {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
        $this->loadFixtures(array());
        parent::setUp();
    }
    
    protected function tearDown() {
        $this->em->close();
        $this->em = null;        
        parent::tearDown();
    }
    
    public function testExecuteNormal() {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new CreateUserCommand());
        $command = $application->get('fos:user:create');
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
			'command' => 'fos:user:create',
			'email' => 'bob@example.com',
			'password' => 'secret',
			'fullname' => 'Bob Terwilliger',
			'institution' => 'Springfield State Penn',
		));
		
		$this->em->clear();
		$user = $this->em->getRepository(User::class)->findOneBy(array(
			'email' => 'bob@example.com',
		));
        
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('bob@example.com', $user->getUsername());
		$this->assertEquals('bob@example.com', $user->getEmail());
		$this->assertEquals('Bob Terwilliger', $user->getFullname());
		$this->assertEquals('Springfield State Penn', $user->getInstitution());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
	}
    
    public function testExecuteSuper() {
        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->add(new CreateUserCommand());
        $command = $application->get('fos:user:create');
        
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
			'command' => 'fos:user:create',
			'email' => 'bob@example.com',
			'password' => 'secret',
			'fullname' => 'Bob Terwilliger',
			'institution' => 'Springfield State Penn',
            '--super-admin' => true
        ));
		
		$this->em->clear();
		$user = $this->em->getRepository(User::class)->findOneBy(array(
			'email' => 'bob@example.com',
		));
        
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('bob@example.com', $user->getUsername());
		$this->assertEquals('bob@example.com', $user->getEmail());
		$this->assertEquals('Bob Terwilliger', $user->getFullname());
		$this->assertEquals('Springfield State Penn', $user->getInstitution());
        $this->assertEquals(['ROLE_SUPER_ADMIN', 'ROLE_USER'], $user->getRoles());
	}
} 

