<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Service;

use Exception;
use Imagick;
use ImagickPixel;
use Nines\MediaBundle\Entity\Image;
use Psr\Log\LoggerInterface;

/**
 * Description of Thumbnailer.
 *
 * @author mjoyce
 */
class Thumbnailer {
    private $width;

    private $height;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function setWidth($width) : void {
        $this->width = $width;
    }

    public function setHeight($height) : void {
        $this->height = $height;
    }

    public function thumbnail(Image $image) {
        $file = $image->getFile();
        $thumbname = $file->getBasename('.' . $file->getExtension()) . '_tn.png';

        $magick = new Imagick($file->getPathname());
        $magick->setBackgroundColor(new ImagickPixel('white'));
        $magick->thumbnailImage($this->width, $this->height, true, false);
        $magick->setImageFormat('png32');
        $path = $file->getPath() . '/' . $thumbname;

        $handle = fopen($path, 'wb');
        if ( ! $handle) {
            $error = error_get_last();

            throw new Exception("Cannot open {$path} for write. " . $error['message']);
        }
        fwrite($handle, $magick->getimageblob());

        return $thumbname;
    }
}
