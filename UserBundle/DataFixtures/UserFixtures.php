<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface {
    public const ADMIN = [
        'username' => 'admin@example.com',
        'password' => 'supersecret',
    ];

    public const USER = [
        'username' => 'user@example.com',
        'password' => 'secret',
    ];

    public const INACTIVE = [
        'username' => 'inactive@example.com',
        'password' => 'sleeping',
    ];

    private ?UserPasswordEncoderInterface $encoder = null;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $manager) : void {
        $admin = new User();
        $admin->setEmail(self::ADMIN['username']);
        $admin->setFullname('Admin user');
        $admin->setAffiliation('Institution');
        $admin->setPassword($this->encoder->encodePassword($admin, self::ADMIN['password']));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setActive(true);
        $this->setReference('user.admin', $admin);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail(self::USER['username']);
        $user->setFullname('Unprivileged user');
        $user->setAffiliation('Department');
        $user->setPassword($this->encoder->encodePassword($user, self::USER['password']));
        $user->setActive(true);
        $this->setReference('user.user', $user);
        $manager->persist($user);

        $inactive = new User();
        $inactive->setEmail(self::INACTIVE['username']);
        $inactive->setFullname('Inactive User');
        $inactive->setAffiliation('None');
        $inactive->setPassword($this->encoder->encodePassword($inactive, self::INACTIVE['password']));
        $inactive->setActive(false);
        $this->setReference('user.inactive', $inactive);
        $manager->persist($inactive);

        $manager->flush();
    }
}
