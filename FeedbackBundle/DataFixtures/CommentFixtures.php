<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\BlogBundle\DataFixtures\PageFixtures;
use Nines\BlogBundle\DataFixtures\PostFixtures;
use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Entity\Post;
use Nines\FeedbackBundle\Entity\Comment;

class CommentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) : void {
        for ($i = 1; $i <= 5; $i++) {
            $fixture = new Comment();
            $fixture->setFullname('Fullname ' . $i);
            $fixture->setEmail('email_' . $i . '@example.com');
            $fixture->setFollowUp(0 === $i % 2);
            $fixture->setEntity((0 === $i % 2 ? Post::class : Page::class) . ':' . $i);
            $fixture->setContent("<p>This is paragraph {$i}</p>");
            $fixture->setStatus($this->getReference('commentstatus.' . $i));
            $manager->persist($fixture);
            $this->setReference('comment.' . $i, $fixture);
        }
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string>
     */
    public function getDependencies() : array {
        return [
            CommentStatusFixtures::class,
            PageFixtures::class,
            PostFixtures::class,
        ];
    }
}
