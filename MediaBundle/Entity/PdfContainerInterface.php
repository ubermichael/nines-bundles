<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface PdfContainerInterface {
    /**
     * @return $this
     */
    public function addPdf(Pdf $pdf) : self;

    /**
     * @return $this
     */
    public function removePdf(Pdf $pdf) : self;

    /**
     * @param Collection|Pdf[] $pdfs
     *
     * @return $this
     */
    public function setPdfs($pdfs) : self;

    /**
     * @return Collection|Pdf[]
     */
    public function getPdfs();
}
