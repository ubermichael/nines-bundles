<?php

namespace Nines\MediaBundle\DataFixtures;

use Nines\MediaBundle\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Imagick;
use ImagickPixel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageFixtures extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {

            $image = new Imagick();
            $hue = $i * 20;
            $image->newImage(640,480,new ImagickPixel("hsb({$hue}%, 100%,  75%)"));
            $image->setImageFormat('png');
            $tmp = tmpfile();
            fwrite($tmp, $image->getImageBlob());
            $upload = new UploadedFile(stream_get_meta_data($tmp)['uri'], "image_{$i}.png", 'image/png', null, true);

            $fixture = new Image();
            $fixture->setImageFile($upload);
            $fixture->setPublic($i % 2 == 0);
            $fixture->setOriginalName('OriginalName ' . $i);
            $fixture->setImagePath('ImagePath ' . $i);
            $fixture->setThumbPath('ThumbPath ' . $i);
            $fixture->setImageSize($i);
            $fixture->setImageWidth($i);
            $fixture->setImageHeight($i);
            $fixture->setDescription("<p>This is paragraph ${i}</p>");
            $fixture->setLicense("<p>This is paragraph ${i}</p>");
            $fixture->setEntity('stdClass:' . $i);

            $em->persist($fixture);
            $this->setReference('image.' . $i, $fixture);
        }
        $em->flush();
    }


}
