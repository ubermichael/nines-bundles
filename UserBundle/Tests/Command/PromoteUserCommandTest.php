<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PromoteUserCommandTest extends KernelTestCase {
    use FixturesTrait;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function testExecute() : void {
        $this->tester->execute([
            'email' => 'user@example.com',
            'role' => 'ROLE_ADMIN',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Role ROLE_ADMIN added to user user@example.com.', $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'user@example.com',
        ]);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
    }

    public function testExecuteAlreadyActive() : void {
        $this->tester->execute([
            'email' => 'admin@example.com',
            'role' => 'ROLE_ADMIN',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Role ROLE_ADMIN added to user admin@example.com.', $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'admin@example.com',
        ]);
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
    }

    public function testExecuteNotFound() : void {
        $this->tester->execute([
            'email' => 'notauser@example.com',
            'role' => 'ROLE_FOO',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Cannot find user notauser@example.com.', $output);
    }

    protected function setUp() : void {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('nines:promote:user');
        $this->tester = new CommandTester($command);

        $this->entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        $this->loadFixtures([
            UserFixtures::class,
        ]);
    }
}
