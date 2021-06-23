<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=AudioRepository::class)
 */
class Audio extends AbstractEntity {
    /**
     * @var File
     */
    private $audioFile;

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
    private $audioPath;

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

    /**
     * @var string
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    private $entity;

    public function __construct() {
        parent::__construct();
    }

    public function __toString() : string {
        if ($this->audioFile) {
            return $this->audioFile->getFilename();
        }
        if ($this->id) {
            return (string) $this->id;
        }

        return '';
    }

    public function getAudioFile() : ?File {
        return $this->audioFile;
    }

    public function setAudioFile(File $file) : self {
        $this->audioFile = $file;

        return $this;
    }

    public function getExtension() : ?string {
        if ( ! $this->audioFile) {
            return null;
        }

        return $this->audioFile->getExtension();
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

    public function getAudioPath() : ?string {
        return $this->audioPath;
    }

    public function setAudioPath(string $audioPath) : self {
        $this->audioPath = $audioPath;

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

    public function getEntity() : ?string {
        return $this->entity;
    }

    public function setEntity($entity) : void {
        if (is_string($entity)) {
            $this->entity = $entity;

            return;
        }
        if ( ! $entity->getId()) {
            throw new Exception('Audio entities must be persisted.');
        }
        $this->entity = ClassUtils::getClass($entity) . ':' . $entity->getId();
    }
}
