<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

trait StoredFileTrait {
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $description = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $license = null;

    private ?File $file = null;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private bool $public = false;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private ?string $originalName = null;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private ?string $path = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=false)
     */
    private ?string $mimeType = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $fileSize = null;

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

    /**
     * @codeCoverageIgnore
     */
    public function getDescription() : ?string {
        return $this->description;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDescription(?string $description) : self {
        $this->description = $description;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getLicense() : ?string {
        return $this->license;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setLicense(?string $license) : self {
        $this->license = $license;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFile() : ?File {
        return $this->file;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setFile(?File $file) : self {
        $this->file = $file;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getExtension() : ?string {
        if ( ! $this->file) {
            return null;
        }

        return $this->file->getExtension();
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPublic() : ?bool {
        return $this->public;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPublic(bool $public) : self {
        $this->public = $public;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getOriginalName() : ?string {
        return $this->originalName;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setOriginalName(?string $originalName) : self {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPath() : ?string {
        return $this->path;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPath(?string $path) : self {
        $this->path = $path;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getMimeType() : ?string {
        return $this->mimeType;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setMimeType(?string $mimeType) : self {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getFileSize() : ?int {
        return $this->fileSize;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setFileSize(?int $fileSize) : self {
        $this->fileSize = $fileSize;

        return $this;
    }
}
