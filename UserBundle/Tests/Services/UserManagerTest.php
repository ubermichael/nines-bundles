<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Tests\Services;

use DateTimeImmutable;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Nines\UtilBundle\Tests\ServiceBaseCase;
use Psr\Log\LoggerInterface;

class UserManagerTest extends ServiceBaseCase {
    /**
     * @var UserManager
     */
    private $manager;

    protected function fixtures() : array {
        return [
            UserFixtures::class,
        ];
    }

    public function testConfig() : void {
        $this->assertInstanceOf(UserManager::class, $this->manager);
    }

    public function testFind() : void {
        $user = $this->manager->find('user@example.com');
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        $nonUser = $this->manager->find('foo@example.com');
        $this->assertNull($nonUser);
    }

    public function testFindByTokenNull() : void {
        $nonUser = $this->manager->findByToken('abc');
        $this->assertNull($nonUser);
    }

    public function testFindByTokenExpired() : void {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('expired token')
            )
        ;
        $this->manager->setLogger($logger);

        $user = new User();
        $user->setEmail('expired@example.com');
        $user->setFullname('Expired');
        $user->setAffiliation('None');
        $user->setResetToken('abcdef');
        $user->setPassword('terriblerawpassword');
        $user->setResetExpiry(new DateTimeImmutable('-2 days'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $resetting = $this->manager->findByToken('abcdef');
        $this->assertNull($resetting);
    }

    public function testFindByToken() : void {
        $user = new User();
        $user->setEmail('valid@example.com');
        $user->setFullname('Valid');
        $user->setAffiliation('None');
        $user->setResetToken('abcdef');
        $user->setPassword('terriblerawpassword');
        $user->setResetExpiry(new DateTimeImmutable('+1 days'));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $resetting = $this->manager->findByToken('abcdef');
        $this->assertNotNull($resetting);
    }

    public function testGeneratePassword() : void {
        $password = $this->manager->generatePassword();
        $this->assertGreaterThan(20, mb_strlen($password));
    }

    public function testGenerateToken() : void {
        $token = $this->manager->generateToken();
        $this->assertGreaterThan(20, mb_strlen($token));
    }

    public function testReset() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.inactive');
        $this->manager->requestReset($user);
        $this->assertGreaterThan(20, mb_strlen($user->getResetToken()));
        $this->assertGreaterThan(new DateTimeImmutable(), $user->getResetExpiry());
    }

    public function testEncodePassword() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $encoded = $this->manager->encodePassword($user, 'abcdefg');
        $this->assertGreaterThan(30, mb_strlen($encoded));
    }

    public function testChangePassword() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->manager->changePassword($user, 'abcdefg');
        $this->assertGreaterThan(30, mb_strlen($user->getPassword()));
    }

    public function testValidatePassword() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->assertTrue($this->manager->validatePassword($user, 'secret'));
    }

    public function testValidatePasswordFail() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->assertFalse($this->manager->validatePassword($user, 'wrongpassword'));
    }

    public function testPromote() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->manager->promote($user, 'ROLE_ADMIN');
        $this->assertTrue($user->hasRole('ROLE_ADMIN'));
    }

    public function testPromoteBadRole() : void {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Unknown role')
            )
        ;
        $this->manager->setLogger($logger);

        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->manager->promote($user, 'ROLE_CHEESE_ADMIN');
        $this->assertTrue($user->hasRole('ROLE_CHEESE_ADMIN'));
    }

    public function testDemote() : void {
        /** @var User $user */
        $user = $this->references->getReference('user.admin');
        $this->manager->demote($user, 'ROLE_ADMIN');
        $this->assertFalse($user->hasRole('ROLE_ADMIN'));
    }

    public function testDemoteBadRole() : void {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('Unknown role')
            )
        ;
        $this->manager->setLogger($logger);

        /** @var User $user */
        $user = $this->references->getReference('user.user');
        $this->manager->demote($user, 'ROLE_CHEESE_ADMIN');
        $this->assertFalse($user->hasRole('ROLE_CHEESE_ADMIN'));
    }

    protected function setUp() : void {
        parent::setUp();
        $this->manager = self::$container->get(UserManager::class);
    }
}
