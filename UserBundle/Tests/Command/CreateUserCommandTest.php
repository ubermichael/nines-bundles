<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Command;

use Nines\UserBundle\Repository\UserRepository;
use Nines\UtilBundle\TestCase\CommandTestCase;

class CreateUserCommandTest extends CommandTestCase {
    private UserRepository $repo;

    public function testExecute() : void {
        $output = $this->execute('nines:user:create', [
            'fullname' => 'New User',
            'email' => 'new@example.com',
            'affiliation' => 'test',
        ]);

        $this->assertSame("Account new@example.com created, but not active.\n", $output);
        $user = $this->repo->findOneByEmail('new@example.com');
        $this->assertNotNull($user);
        $this->assertFalse($user->isActive());
    }

    protected function setUp() : void {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->repo = self::$container->get(UserRepository::class);
    }
}
