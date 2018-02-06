<?php

namespace Nines\BlogBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\Post;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;

class LoadPosts extends Fixture implements DependentFixtureInterface {

    public function load(ObjectManager $manager) {
        $draft = new Post();
        $draft->setTitle("Hello draft.");
        $draft->setCategory($this->getReference('post-category-1'));
        $draft->setStatus($this->getReference('post-status-1'));
        $draft->setExcerpt("I am draft excerpt.");
        $draft->setContent("I am an excerpt and I like drafts.");
        $draft->setSearchable("I am an excerpt and I like drafts.");
        $draft->setUser($this->getReference('user.user'));
        $manager->persist($draft);
        
        $published = new Post();
        $published->setTitle("Hello world.");
        $published->setCategory($this->getReference('post-category-1'));
        $published->setStatus($this->getReference('post-status-1'));
        $published->setExcerpt("I am published excerpt.");
        $published->setContent("I am an excerpt and I like publishing.");
        $published->setSearchable("I am an excerpt and I like publishing.");
        $published->setUser($this->getReference('user.user'));
        $manager->persist($published);
        $manager->flush();
    }

    public function getDependencies() {
        return [
            LoadUser::class,
            LoadStatuses::class,
            LoadCategories::class,
        ];
    }

}
