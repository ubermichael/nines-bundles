<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Entity;

use Nines\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase {
    public function testDefaultRoles() : void {
        $user = new User();
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testAddRoles() : void {
        $user = new User();
        $user->addRole('ROLE_FOO');
        $this->assertSame(['ROLE_USER', 'ROLE_FOO'], $user->getRoles());

        $user->addRole('ROLE_FOO');
        $this->assertSame(['ROLE_USER', 'ROLE_FOO'], $user->getRoles());

        $user->addRole('ROLE_USER');
        $this->assertSame(['ROLE_USER', 'ROLE_FOO'], $user->getRoles());
    }

    public function testSetRoles() : void {
        $user = new User();
        $user->setRoles(['A', 'B', 'C']);
        $this->assertSame(['A', 'B', 'C', 'ROLE_USER'], $user->getRoles());

        $user->setRoles([]);
        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testHasRole() : void {
        $user = new User();
        $user->setRoles(['A', 'B', 'C']);
        $this->assertTrue($user->hasRole('A'));
        $this->assertFalse($user->hasRole('cheeseburger'));
    }

    public function testRemoveRoles() : void {
        $user = new User();
        $user->setRoles(['A', 'B', 'C']);
        $this->assertSame(['A', 'B', 'C', 'ROLE_USER'], $user->getRoles());

        $user->removeRole('D');
        $this->assertSame(['A', 'B', 'C', 'ROLE_USER'], $user->getRoles());

        $user->removeRole('B');
        $this->assertSame(['A', 'C', 'ROLE_USER'], $user->getRoles());

        $user->removeRole('ROLE_USER');
        $this->assertSame(['A', 'C', 'ROLE_USER'], $user->getRoles());
    }
}
