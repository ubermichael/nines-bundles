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
use Nines\UtilBundle\Entity\LinkedEntityInterface;
use Nines\UtilBundle\Entity\LinkedEntityTrait;

/**
 * @ORM\Entity(repositoryClass=PdfRepository::class)
 * @ORM\Table(name="nines_media_pdf", indexes={
 *     @ORM\Index(name="nines_media_pdf_ft", columns={"original_name", "description"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Pdf extends AbstractPdf implements LinkedEntityInterface, StoredFileInterface {
    use LinkedEntityTrait;
}
