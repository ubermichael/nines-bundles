<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Nines\DublinCoreBundle\Entity\Value;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use stdClass;

class ValueFixtures extends Fixture implements DependentFixtureInterface {
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 1; $i <= 4; $i++) {
            $fixture = new Value();
            $fixture->setData("Data " . $i);
            $fixture->setEntity(stdClass::class . ':' . $i);
            $fixture->setElement($this->getReference('dc_title'));
            $em->persist($fixture);
            $this->setReference('value.' . $i, $fixture);
        }
        $em->flush();
    }

    public function getDependencies() : array {
        return [
            ElementFixtures::class,
        ];
    }
}
