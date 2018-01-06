<?php

namespace Nines\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\UserBundle\Entity\User;

/**
 * Load some users for unit tests.
 */
class LoadUser extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        $admin = new User();
        $admin->setEmail("admin@example.com");
        $admin->setFullname("Admin user");
        $admin->setUsername("admin@example.com");
        $admin->setPlainPassword("supersecret");
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setEnabled(true);
        $em->persist($admin);

        $user = new User();
        $user->setEmail("user@example.com");
        $user->setFullname("Unprivileged user");
        $user->setUsername("user@example.com");
        $user->setPlainPassword("secret");
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();
    }
}
