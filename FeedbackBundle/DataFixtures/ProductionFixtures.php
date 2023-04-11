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
use Doctrine\Persistence\ObjectManager;
use Nines\FeedbackBundle\Entity\CommentStatus;

class ProductionFixtures extends Fixture implements FixtureGroupInterface {
    /**
     * {@inheritdoc}
     */
    public static function getGroups() : array {
        return [
            'prod',
        ];
    }

    public function load(ObjectManager $manager) : void {
        $submitted = new CommentStatus();
        $submitted->setLabel('Submitted');
        $submitted->setDescription('Comments which have not been approved for publication');
        $manager->persist($submitted);

        $published = new CommentStatus();
        $published->setLabel('Published');
        $published->setDescription('Comments which have been approved for publication');
        $manager->persist($published);

        $manager->flush();
    }
}
