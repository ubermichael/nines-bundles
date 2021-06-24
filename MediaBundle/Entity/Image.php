<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"original_name", "description"}, flags={"fulltext"})
 * })
 */
class Image extends AbstractEntity implements EntityReferenceInterface, StoredFileInterface {
    use EntityReferenceTrait;
    use StoredFileTrait;

    /**
     * @var File
     */
    protected $thumbFile;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $thumbPath;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $imageWidth;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $imageHeight;

    public function __construct() {
        parent::__construct();
    }

    public function setThumbFile(File $file) : self {
        $this->thumbFile = $file;

        return $this;
    }

    public function getThumbFile() : ?File {
        return $this->thumbFile;
    }

    public function getThumbPath() : string {
        return $this->thumbPath;
    }

    public function setThumbPath(string $thumbPath) : self {
        $this->thumbPath = $thumbPath;

        return $this;
    }

    public function getImageWidth() : int {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth) : self {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    public function getImageHeight() : int {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight) : self {
        $this->imageHeight = $imageHeight;

        return $this;
    }
}
