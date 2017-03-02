<?php

namespace Nines\UserBundle\Tests\DataFixtures\ORM;

use Nines\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Load some users for unit tests.
 */
class LoadUsers implements FixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $admin = new User();
        $admin->setEmail("admin@example.com");
        $admin->setFullname("Admin user");
        $admin->setUsername("admin@example.com");
        $admin->setPlainPassword("supersecret");
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setEnabled(true);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail("user@example.com");
        $user->setFullname("Unprivileged user");
        $user->setUsername("user@example.com");
        $user->setPlainPassword("secret");
        $user->setEnabled(true);
        $manager->persist($user);
        $manager->flush();
    }
}
