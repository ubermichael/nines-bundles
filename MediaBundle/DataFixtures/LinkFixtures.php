<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Link;

class LinkFixtures extends Fixture {
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 1; $i <= 4; $i++) {
            $fixture = new Link();

            $fixture->setText("Text {$i}");
            $fixture->setUrl("https://example.com/{$i}");
            $fixture->setEntity('stdClass:' . $i);

            $em->persist($fixture);
            $this->setReference('link.' . $i, $fixture);
        }
        $em->flush();
    }
}
