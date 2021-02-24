<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @ORM\Table(indexes={
 *  @ORM\Index(columns={"original_name", "description"}, flags={"fulltext"})
 * })
 */
class Image extends AbstractEntity {
    /**
     * @var File
     */
    protected $imageFile;

    /**
     * @var File
     */
    protected $thumbFile;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $public;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    protected $originalName;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $imagePath;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $thumbPath;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $imageSize;

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

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $license;

    /**
     * @var string
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    private $entity;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $mimeType;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->imageFile->getFilename();
    }

    public function setThumbFile(File $file) : self {
        $this->thumbFile = $file;

        return $this;
    }

    public function getThumbFile() : ?File {
        return $this->thumbFile;
    }

    public function setImageFile(File $file) : self {
        $this->imageFile = $file;

        return $this;
    }

    public function getImageFile() : ?File {
        return $this->imageFile;
    }

    public function getPublic() : ?bool {
        return $this->public;
    }

    public function setPublic(bool $public) : self {
        $this->public = $public;

        return $this;
    }

    public function getOriginalName() : ?string {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName) : self {
        $this->originalName = $originalName;

        return $this;
    }

    public function getImagePath() : ?string {
        return $this->imagePath;
    }

    public function setImagePath(string $imagePath) : self {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getThumbPath() : ?string {
        return $this->thumbPath;
    }

    public function setThumbPath(string $thumbPath) : self {
        $this->thumbPath = $thumbPath;

        return $this;
    }

    public function getImageSize() : ?int {
        return $this->imageSize;
    }

    public function setImageSize(int $imageSize) : self {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getImageWidth() : ?int {
        return $this->imageWidth;
    }

    public function setImageWidth(int $imageWidth) : self {
        $this->imageWidth = $imageWidth;

        return $this;
    }

    public function getImageHeight() : ?int {
        return $this->imageHeight;
    }

    public function setImageHeight(int $imageHeight) : self {
        $this->imageHeight = $imageHeight;

        return $this;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(?string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getLicense() : ?string {
        return $this->license;
    }

    public function setLicense(?string $license) : self {
        $this->license = $license;

        return $this;
    }

    public function getMimeType() : ?string {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType) : self {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getEntity() : ?string {
        return $this->entity;
    }

    public function setEntity($entity) : void {
        if(is_string($entity)) {
            $this->entity = $entity;
            return;
        }
        if ( ! $entity->getId()) {
            throw new Exception('Image entities must be persisted.');
        }
        $this->entity = ClassUtils::getClass($entity) . ':' . $entity->getId();
    }
}
