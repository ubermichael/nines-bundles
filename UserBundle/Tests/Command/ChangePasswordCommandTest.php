<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Command;

use Nines\UserBundle\Repository\UserRepository;
use Nines\UserBundle\Services\UserManager;
use Nines\UtilBundle\TestCase\CommandTestCase;

class ChangePasswordCommandTest extends CommandTestCase {
    private UserRepository $repo;

    private UserManager $manager;

    public function testExecute() : void {
        $output = $this->execute('nines:user:password', [
            'email' => 'inactive@example.com',
            'password' => 'abc123',
        ]);

        $this->assertSame("Password for inactive@example.com changed.\n", $output);
        $user = $this->repo->findOneByEmail('inactive@example.com');
        $this->assertTrue($this->manager->validatePassword($user, 'abc123'));
    }

    protected function setUp() : void {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->repo = self::$container->get(UserRepository::class);
        $this->manager = self::$container->get(UserManager::class);
    }
}
