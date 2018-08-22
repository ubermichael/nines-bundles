<?php

namespace Nines\BlogBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\Page;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;

class LoadPage extends Fixture implements DependentFixtureInterface {

    public function load(ObjectManager $manager) {
        $draft = new Page();
        $draft->setTitle("Hello draft.");
        $draft->setPublic(false);
        $draft->setExcerpt("I am draft excerpt.");
        $draft->setContent("I am an excerpt and I like drafts.");
        $draft->setSearchable("I am an excerpt and I like drafts.");
        $draft->setUser($this->getReference('user.user'));
        $manager->persist($draft);

        $published = new Page();
        $published->setTitle("Hello world.");
        $published->setPublic(true);
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
        ];
    }

}
