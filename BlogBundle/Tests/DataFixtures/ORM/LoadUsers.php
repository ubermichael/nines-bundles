<?php

namespace Nines\BlogBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\UserBundle\Entity\User;

/**
 * Load some users for unit tests.
 */
class LoadUsers extends AbstractFixture implements OrderedFixtureInterface {

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
        $this->addReference('blog-user-admin', $admin);

        $user = new User();
        $user->setEmail("user@example.com");
        $user->setFullname("Unprivileged user");
        $user->setUsername("user@example.com");
        $user->setPlainPassword("secret");
        $user->setEnabled(true);
        $manager->persist($user);
        $this->addReference('blog-user-user', $user);
        
        $manager->flush();
    }

    public function getOrder() {
        return 1;
    }

}
