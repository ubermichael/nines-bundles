<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\Table(name="nines_media_image", indexes={
 *     @ORM\Index(name="nines_media_image_ft", columns={"original_name", "description"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Image extends AbstractEntity implements LinkedEntityInterface, StoredFileInterface {
    use LinkedEntityTrait;

    use StoredFileTrait;

    protected ?File $thumbFile = null;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected ?string $thumbPath = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected ?int $imageWidth = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected ?int $imageHeight = null;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @codeCoverageIgnore
     */
    public function setThumbFile(File $file) : self {
        $this->thumbFile = $file;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getThumbFile() : ?File {
        return $this->thumbFile;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getThumbPath() : string {
        return $this->thumbPath;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setThumbPath(string $thumbPath) : self {
        $this->thumbPath = $thumbPath;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getImageWidth() : int {
        return $this->imageWidth;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setImageWidth(int $imageWidth) : self {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getImageHeight() : int {
        return $this->imageHeight;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setImageHeight(int $imageHeight) : self {
        $this->imageHeight = $imageHeight;

        return $this;
    }
}
