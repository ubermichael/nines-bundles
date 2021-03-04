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

class ActivateUserCommandTest extends KernelTestCase {
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
            'email' => 'inactive@example.com',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Account inactive@example.com is active.', $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'inactive@example.com',
        ]);
        $this->assertNotNull($user);
        $this->assertTrue($user->isActive());
    }

    public function testExecuteAlreadyActive() : void {
        $this->tester->execute([
            'email' => 'user@example.com',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Account user@example.com is active.', $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'user@example.com',
        ]);
        $this->assertNotNull($user);
        $this->assertTrue($user->isActive());
    }

    public function testExecuteNotFound() : void {
        $this->tester->execute([
            'email' => 'notauser@example.com',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Cannot find user notauser@example.com.', $output);
    }

    protected function setUp() : void {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('nines:activate:user');
        $this->tester = new CommandTester($command);

        $this->entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        $this->loadFixtures([
            UserFixtures::class,
        ]);
    }
}
