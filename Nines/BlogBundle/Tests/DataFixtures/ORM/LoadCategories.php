<?php

namespace Nines\BlogBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostCategory;

/**
 * Load some users for unit tests.
 */
class LoadCategories extends AbstractFixture implements OrderedFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) {
        $category = new PostCategory();
        $category->setName('announcement');
        $category->setLabel('Announcement');
        $category->setDescription('Stuff happened.');
        $manager->persist($category);
        $manager->flush();
        
        $this->addReference('post-cat-announcement', $category);
    }

    public function getOrder() {
        return 1;
    }

}
