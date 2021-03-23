<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostCategory;

/**
 * Load some users for unit tests.
 */
class PostCategoryFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return [
            'nines_blog',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) : void {
        $category = new PostCategory();
        $category->setName('announcement');
        $category->setLabel('Announcement');
        $category->setDescription('Stuff happened.');
        $manager->persist($category);
        $manager->flush();

        $this->addReference('post-category-1', $category);
    }
}
