<?php

namespace Nines\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\UserBundle\Entity\User;

/**
 * Load some users for unit tests.
 */
class LoadUser extends Fixture {

    const ADMIN = array(
        'username' => 'admin@example.com',
        'password' => 'supersecret',
    );

    const USER = array(
        'username' => 'user@example.com',
        'password' => 'secret',
    );

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $admin = new User();
        $admin->setEmail(self::ADMIN['username']);
        $admin->setFullname("Admin user");
        $admin->setUsername(self::ADMIN['username']);
        $admin->setPlainPassword(self::ADMIN['password']);
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setEnabled(true);
        $this->setReference('user.admin', $admin);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail(self::USER['username']);
        $user->setFullname("Unprivileged user");
        $user->setUsername(self::USER['username']);
        $user->setPlainPassword(self::USER['password']);
        $user->setEnabled(true);
        $this->setReference('user.user', $admin);
        $manager->persist($user);
        $manager->flush();
    }
}
