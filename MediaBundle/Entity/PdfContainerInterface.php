<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Nines\UtilBundle\Entity\AbstractEntityInterface;

interface PdfContainerInterface extends AbstractEntityInterface {
    public function addPdf(Pdf $pdf) : self;

    public function removePdf(Pdf $pdf) : self;

    /**
     * @param array<Pdf>|Collection<int,Pdf> $pdfs
     */
    public function setPdfs($pdfs) : self;

    public function containsPdf(Pdf $pdf) : bool;

    /**
     * @return array<Pdf>
     */
    public function getPdfs() : array;
}
