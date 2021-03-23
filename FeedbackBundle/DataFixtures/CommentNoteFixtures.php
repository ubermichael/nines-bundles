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
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\UserBundle\DataFixtures\UserFixtures;

/**
 * Load some users for unit tests.
 */
class CommentNoteFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
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
        $note = new CommentNote();
        $note->setComment($this->getReference('comment.1'));
        $note->setContent('This is a note.');
        $note->setUser($this->getReference('user.user'));
        $em->persist($note);
        $em->flush();
    }

    public function getDependencies() {
        return [
            UserFixtures::class,
            CommentFixtures::class,
        ];
    }
}
