<?php

namespace Nines\UserBundle\Tests\Command;

use Nines\UserBundle\Command\CreateUserCommand;
use Nines\UserBundle\Entity\User;
use Nines\UtilBundle\Tests\Util\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateUserCommandTest extends BaseTestCase {

    public function getFixtures() {
        return [];
    }

    public function testExecuteNormal() {
        self::bootKernel();
        $application = new Application(self::$kernel);        
        $command = $application->find('fos:user:create');

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
        $command = $application->find('fos:user:create');

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

