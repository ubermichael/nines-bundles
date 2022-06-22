<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DataFixtures;

use App\Entity\Recording;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\AudioManager;
use stdClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AudioFixtures extends Fixture implements FixtureGroupInterface {
    public const FILES = [
        '259692__nsmusic__santur-arpegio.mp3',
        '390587__carloscarty__pan-flute-02.mp3',
        '391691__jpolito__jp-rainloop12.mp3',
        '443027__pramonette__thunder-long.mp3',
        '94934__bletort__taegum-1.mp3',
    ];

    private ?AudioManager $manager = null;

    public static function getGroups() : array {
        return ['test', 'dev'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) : void {
        $this->manager->setCopy(true);
        for ($i = 1; $i <= 5; $i++) {
            $file = self::FILES[$i - 1];
            $upload = new UploadedFile(dirname(__DIR__, 2) . '/MediaBundle/Tests/data/audio/' . $file, $file, 'audio/mp3', null, true);
            $audio = new Audio();
            $audio->setFile($upload);
            $audio->setPublic(0 === $i % 2);
            $audio->setOriginalName($file);
            $audio->setDescription("<p>This is paragraph {$i}</p>");
            $audio->setLicense("<p>This is paragraph {$i}</p>");
            $audio->setEntity(stdClass::class . ':' . $i);
            $manager->persist($audio);
            $manager->flush();
        }
        $this->manager->setCopy(false);
    }

    /**
     * @required
     */
    public function setManager(AudioManager $manager) : void {
        $this->manager = $manager;
    }
}
