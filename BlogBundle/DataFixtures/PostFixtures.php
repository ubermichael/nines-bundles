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
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\Post;
use Nines\UserBundle\DataFixtures\UserFixtures;

class PostFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return [
            'nines_blog',
        ];
    }

    public function load(ObjectManager $manager) : void {
        $draft = new Post();
        $draft->setTitle('Hello draft.');
        $draft->setCategory($this->getReference('post-category-1'));
        $draft->setStatus($this->getReference('post-status-1'));
        $draft->setExcerpt('I am draft excerpt.');
        $draft->setContent('I am an excerpt and I like drafts.');
        $draft->setSearchable('I am an excerpt and I like drafts.');
        $draft->setUser($this->getReference('user.user'));
        $this->setReference('post.draft', $draft);
        $manager->persist($draft);

        $published = new Post();
        $published->setTitle('Hello world.');
        $published->setCategory($this->getReference('post-category-1'));
        $published->setStatus($this->getReference('post-status-1'));
        $published->setExcerpt('I am published excerpt.');
        $published->setContent('I am an excerpt and I like publishing.');
        $published->setSearchable('I am an excerpt and I like publishing.');
        $published->setUser($this->getReference('user.user'));
        $this->setReference('post.published', $published);
        $manager->persist($published);
        $manager->flush();
    }

    public function getDependencies() {
        return [
            UserFixtures::class,
            PostStatusFixtures::class,
            PostCategoryFixtures::class,
        ];
    }
}
