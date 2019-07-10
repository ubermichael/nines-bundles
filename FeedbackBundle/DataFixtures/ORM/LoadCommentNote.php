<?php

namespace Nines\FeedbackBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\UserBundle\DataFixtures\ORM\LoadUser;

/**
 * Load some users for unit tests.
 */
class LoadCommentNote extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager) {

        $note = new CommentNote();
        $note->setComment($this->getReference('comment.1'));
        $note->setContent('This is a note.');
        $note->setUser($this->getReference('user.user'));
        $manager->persist($note);
        $manager->flush();
    }

    public function getDependencies() {
        return array(
            LoadUser::class,
            LoadComment::class,
        );
    }

}
