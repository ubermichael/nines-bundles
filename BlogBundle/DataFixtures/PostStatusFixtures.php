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
use Nines\BlogBundle\Entity\PostStatus;

/**
 * Load some users for unit tests.
 */
class PostStatusFixtures extends Fixture implements FixtureGroupInterface {
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
        $repo = $manager->getRepository(PostStatus::class);
        $draft = $repo->findOneBy([
            'name' => 'draft',
        ]);
        if ( ! $draft) {
            $draft = new PostStatus();
            $draft->setName('draft');
            $draft->setLabel('Draft');
            $draft->setPublic(false);
            $draft->setDescription('Drafty');
            $manager->persist($draft);
        }
        $this->addReference('post-status-1', $draft);

        $published = $repo->findOneBy([
            'name' => 'published',
        ]);
        if ( ! $published) {
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
}
