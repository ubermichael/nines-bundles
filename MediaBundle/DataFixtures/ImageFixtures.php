<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Imagick;
use ImagickPixel;
use Nines\MediaBundle\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFixtures extends Fixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $image = new Imagick();
            $hue = $i * 20;
            $image->newImage(640, 480, new ImagickPixel("hsb({$hue}%, 100%,  75%)"));
            $image->setImageFormat('png');
            $tmp = tmpfile();
            fwrite($tmp, $image->getImageBlob());
            $upload = new UploadedFile(stream_get_meta_data($tmp)['uri'], "image_{$i}.png", 'image/png', null, true);

            $fixture = new Image();
            $fixture->setImageFile($upload);
            $fixture->setPublic(0 === $i % 2);
            $fixture->setOriginalName('OriginalName ' . $i);
            $fixture->setImagePath('ImagePath ' . $i);
            $fixture->setThumbPath('ThumbPath ' . $i);
            $fixture->setImageSize($i);
            $fixture->setImageWidth($i);
            $fixture->setImageHeight($i);
            $fixture->setMimeType("image/png");
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setLicense("<p>This is paragraph {$i}</p>");
            $fixture->setEntity('stdClass:' . $i);

            $em->persist($fixture);
            $this->setReference('image.' . $i, $fixture);
        }
        $em->flush();
    }
}
