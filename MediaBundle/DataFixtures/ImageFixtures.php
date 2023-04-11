<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DataFixtures;

use App\Entity\Artefact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Service\ImageManager;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFixtures extends Fixture implements FixtureGroupInterface {
    public const FILES = [
        '28213926366_4430448ff7_c.jpg',
        '30191231240_4010f114ba_c.jpg',
        '33519978964_c025c0da71_c.jpg',
        '3632486652_b432f7b283_c.jpg',
        '49654941212_6e3bb28a75_c.jpg',
    ];

    private ?ImageManager $manager = null;

    public static function getGroups() : array {
        return ['test', 'dev'];
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/MediaBundle/Tests/data/image/' . $file, $file, 'image/jpeg', null, true);
            $image = new Image();
            $image->setFile($upload);
            $image->setPublic(0 === $i % 2);
            $image->setOriginalName($file);
            $image->setDescription("<p>This is paragraph {$i}</p>");
            $image->setLicense("<p>This is paragraph {$i}</p>");
            $image->setEntity(stdClass::class . ':' . $i);
            $manager->persist($image);
            $manager->flush();
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(ImageManager $manager) : void {
        $this->manager = $manager;
    }
}
