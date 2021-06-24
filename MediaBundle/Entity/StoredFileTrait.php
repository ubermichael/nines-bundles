<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

trait StoredFileTrait {
    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description = null;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    protected $license = null;

    /**
     * @var File
     */
    private $file;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $public;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $originalName;

    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $path;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private $mimeType;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $fileSize;

    public function __toString() : string {
        if ($this->file) {
            return $this->file->getFilename();
        }
        if ($this->originalName) {
            return $this->originalName;
        }
        if ($this->path) {
            return $this->path;
        }

        return '';
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getLicense() : ?string {
        return $this->license;
    }

    public function setLicense(string $license) : self {
        $this->license = $license;

        return $this;
    }

    public function getFile() : ?File {
        return $this->file;
    }

    public function setFile(File $file) : self {
        $this->file = $file;

        return $this;
    }

    public function getExtension() : ?string {
        if ( ! $this->file) {
            return null;
        }

        return $this->file->getExtension();
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

    public function getPath() : ?string {
        return $this->path;
    }

    public function setPath(string $path) : self {
        $this->path = $path;

        return $this;
    }

    public function getMimeType() : ?string {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType) : self {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFileSize() : ?int {
        return $this->fileSize;
    }

    public function setFileSize(int $fileSize) : self {
        $this->fileSize = $fileSize;

        return $this;
    }
}
