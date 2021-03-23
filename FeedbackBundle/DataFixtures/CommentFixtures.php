<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
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
use Nines\FeedbackBundle\Entity\Comment;

/**
 * Load some users for unit tests.
 */
class CommentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return [
            'nines_feedback',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        $comment = new Comment();
        $comment->setFullname('Bobby');
        $comment->setFollowUp(false);
        $comment->setContent('Comment 1');
        $comment->setEmail('bob@example.com');
        $comment->setTitle('Title 1');
        $comment->setEntity(Page::class . ':' . $this->getReference('page.published')->getId());
        $comment->setStatus($this->getReference('comment.status.submitted'));
        $em->persist($comment);
        $this->setReference('comment.1', $comment);
        $em->flush();
    }

    public function getDependencies() {
        return [
            CommentStatusFixtures::class,
            PageFixtures::class,
            PostFixtures::class,
        ];
    }
}
