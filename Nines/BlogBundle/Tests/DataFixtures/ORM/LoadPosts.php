<?php

namespace Nines\BlogBundle\Tests\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\Post;

class LoadPosts extends AbstractFixture implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {
        $draft = new Post();
        $draft->setTitle("Hello draft.");
        $draft->setCategory($this->getReference('post-cat-announcement'));
        $draft->setStatus($this->getReference('post-status-draft'));
        $draft->setExcerpt("I am draft excerpt.");
        $draft->setContent("I am an excerpt and I like drafts.");
        $draft->setSearchable("I am an excerpt and I like drafts.");
        $draft->setUser($this->getReference('blog-user-user'));
        $manager->persist($draft);
        
        $published = new Post();
        $published->setTitle("Hello world.");
        $published->setCategory($this->getReference('post-cat-announcement'));
        $published->setStatus($this->getReference('post-status-published'));
        $published->setExcerpt("I am published excerpt.");
        $published->setContent("I am an excerpt and I like publishing.");
        $published->setSearchable("I am an excerpt and I like publishing.");
        $published->setUser($this->getReference('blog-user-user'));
        $manager->persist($published);
        $manager->flush();
    }

    public function getOrder() {
        return 2;
    }

}
