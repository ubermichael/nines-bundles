<?php

namespace Nines\FeedbackBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nines\FeedbackBundle\Entity\CommentStatus;

/**
 * Load some users for unit tests.
 */
class LoadCommentStatus extends Fixture {

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) {

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
