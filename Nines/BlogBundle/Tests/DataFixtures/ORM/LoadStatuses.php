<?php

namespace Nines\BlogBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostStatus;

/**
 * Load some users for unit tests.
 */
class LoadStatuses extends AbstractFixture implements OrderedFixtureInterface {

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
        $this->addReference('post-status-draft', $draft);
        
        $published = new PostStatus();
        $published->setName('published');
        $published->setLabel('Published');
        $published->setPublic(true);
        $published->setDescription('Public');        
        $manager->persist($published);
        $this->setReference('post-status-published', $published);
        $manager->flush();
    }

    public function getOrder() {
        return 1;
    }

}
