<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\PdfRepository;
use Nines\UtilBundle\Entity\AbstractEntity;
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass=PdfRepository::class)
 * @ORM\Table(name="nines_media_pdf", indexes={
 *     @ORM\Index(name="nines_media_pdf_ft", columns={"original_name", "description"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Pdf extends AbstractEntity implements LinkedEntityInterface, StoredFileInterface {
    use LinkedEntityTrait;

    use StoredFileTrait;

    protected ?File $thumbFile = null;

    /**
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected ?string $thumbPath = null;

    /**
     * @codeCoverageIgnore
     */
    public function getThumbFile() : ?File {
        return $this->thumbFile;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setThumbFile(File $thumbFile) : self {
        $this->thumbFile = $thumbFile;

        return $this;
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
}
