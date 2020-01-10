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

class CreateUserCommandTest extends KernelTestCase {
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
            'email' => 'new@example.com',
            'fullname' => 'New User',
            'affiliation' => 'Institution',
        ]);

        $output = $this->tester->getDisplay();
        $this->assertContains('Account new@example.com created, but not active.', $output);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => 'new@example.com',
        ]);
        $this->assertNotNull($user);
        $this->assertFalse($user->isActive());
        $this->assertStringStartsWith('$argon2id$', $user->getPassword());
    }

    /**
     * @expectedException \Doctrine\DBAL\Exception\UniqueConstraintViolationException
     */
    public function testExecuteDuplicate() : void {
        $this->tester->execute([
            'email' => 'user@example.com',
            'fullname' => 'New User',
            'affiliation' => 'Institution',
        ]);
    }

    protected function setUp() : void {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('nines:create:user');
        $this->tester = new CommandTester($command);

        $this->entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

        $this->loadFixtures([
            UserFixtures::class,
        ]);
    }
}
