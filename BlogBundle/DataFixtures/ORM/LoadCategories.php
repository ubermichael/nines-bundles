<?php

namespace Nines\BlogBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostCategory;

/**
 * Load some users for unit tests.
 */
class LoadCategories extends Fixture {

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
        
        $this->addReference('post-category-1', $category);
    }

}
