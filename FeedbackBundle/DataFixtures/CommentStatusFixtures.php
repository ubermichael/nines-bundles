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
use Doctrine\Persistence\ObjectManager;
use Nines\FeedbackBundle\Entity\CommentStatus;

/**
 * Load some users for unit tests.
 */
class CommentStatusFixtures extends Fixture implements FixtureGroupInterface
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
        $submitted = new CommentStatus();
        $submitted->setName('submitted');
        $submitted->setLabel('Submitted');
        $submitted->setDescription('The comment has been submitted, but not yet vetted.');
        $this->setReference('comment.status.submitted', $submitted);
        $em->persist($submitted);

        $unpublished = new CommentStatus();
        $unpublished->setName('unpublished');
        $unpublished->setLabel('Unpublished');
        $unpublished->setDescription('Comment has not been approved for publication.');
        $this->setReference('comment.status.unpublished', $unpublished);
        $em->persist($unpublished);

        $published = new CommentStatus();
        $published->setName('published');
        $published->setLabel('Published');
        $published->setDescription('Comment has been approved for publication.');
        $this->setReference('comment.status.published', $published);
        $em->persist($published);

        $em->flush();
    }
}
