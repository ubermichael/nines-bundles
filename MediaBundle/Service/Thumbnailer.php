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
use ImagickException;
use ImagickPixel;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
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

    /**
     * @throws ImagickException
     */
    protected function thumb(string $from, string $to) : void {
        $magick = new Imagick($from);
        $magick->setBackgroundColor(new ImagickPixel('white'));
        $magick->thumbnailImage($this->width, $this->height, true, false);
        $magick->setImageFormat('png32');
        $magick->writeImage($to);
    }

    /**
     * @throws ImagickException
     */
    protected function thumbnailImage(Image $image) : string {
        $file = $image->getFile();
        $thumbname = $file->getBasename('.' . $file->getExtension()) . '_tn.png';
        $this->thumb($file->getRealPath(), dirname($file->getRealPath()) . '/' . $thumbname);

        return $thumbname;
    }

    /**
     * @throws ImagickException
     */
    protected function thumbnailPdf(Pdf $pdf) : string {
        $file = $pdf->getFile();
        $thumbname = $file->getBasename('.' . $file->getExtension()) . '_tn.png';
        $this->thumb($file->getRealPath() . '[0]', dirname($file->getRealPath()) . '/' . $thumbname);

        return $thumbname;
    }

    public function setWidth($width) : void {
        $this->width = $width;
    }

    public function setHeight($height) : void {
        $this->height = $height;
    }

    /**
     * @param Image|Pdf $item
     *
     * @throws ImagickException
     * @throws Exception
     */
    public function thumbnail($item) : string {
        if ($item instanceof Image) {
            return $this->thumbnailImage($item);
        }
        if ($item instanceof Pdf) {
            return $this->thumbnailPdf($item);
        }

        throw new Exception('Cannot generate thumbnail for ' . get_class($item));
    }

    /**
     * @param Image|Pdf $item
     *
     * @return string
     * @throws ImagickException
     * @throws Exception
     */
    public function thumbnail($item) : string {
        if($item instanceof Image) {
            return $this->thumbnailImage($item);
        } elseif ($item instanceof Pdf) {
            return $this->thumbnailPdf($item);
        } else {
            throw new Exception("Cannot generate thumbnail for " . get_class($item));
        }
    }
}
