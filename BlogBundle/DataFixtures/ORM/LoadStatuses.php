<?php

namespace Nines\BlogBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostStatus;

/**
 * Load some users for unit tests.
 */
class LoadStatuses extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $draft = new PostStatus();
        $draft->setName('draft');
        $draft->setLabel('Draft');
        $draft->setPublic(false);
        $draft->setDescription('Drafty');
        $manager->persist($draft);        
        $this->addReference('post-status-1', $draft);
        
        $published = new PostStatus();
        $published->setName('published');
        $published->setLabel('Published');
        $published->setPublic(true);
        $published->setDescription('Public');        
        $manager->persist($published);
        $this->addReference('post-status-2', $draft);
        $manager->flush();
    }

}
