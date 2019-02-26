<?php

namespace Nines\BlogBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\BlogBundle\Entity\PostStatus;

/**
 * Load some users for unit tests.
 */
class LoadPostStatus extends Fixture implements FixtureGroupInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {
        $repo = $manager->getRepository(PostStatus::class);
        $draft = $repo->findOneBy(array(
            'name' => 'draft',
        ));
        if (!$draft) {
            $draft = new PostStatus();
            $draft->setName('draft');
            $draft->setLabel('Draft');
            $draft->setPublic(false);
            $draft->setDescription('Drafty');
            $manager->persist($draft);
        }
        $this->addReference('post-status-1', $draft);

        $published = $repo->findOneBy(array(
            'name' => 'published',
        ));
        if (!$published) {
            $published = new PostStatus();
            $published->setName('published');
            $published->setLabel('Published');
            $published->setPublic(true);
            $published->setDescription('Public');
            $manager->persist($published);
            $this->addReference('post-status-2', $draft);
        }
        $manager->flush();
    }

    public static function getGroups(): array {
        return array('test', 'setup');
    }

}
