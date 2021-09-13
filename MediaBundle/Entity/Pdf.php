<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\PdfRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=PdfRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"original_name", "description"}, flags={"fulltext"})
 * })
 */
class Pdf extends AbstractEntity implements EntityReferenceInterface, StoredFileInterface {
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

    public function getThumbFile() : ?File {
        return $this->thumbFile;
    }

    public function setThumbFile(File $thumbFile) : self {
        $this->thumbFile = $thumbFile;

        return $this;
    }

    public function getThumbPath() : string {
        return $this->thumbPath;
    }

    public function setThumbPath(string $thumbPath) : self {
        $this->thumbPath = $thumbPath;

        return $this;
    }
}
