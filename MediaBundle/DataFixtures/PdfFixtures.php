<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use FPDF;
use Imagick;
use ImagickPixel;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PdfFixtures extends Fixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {

            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->cell(40,10, "Hello World {$i}.");
            $tmp = tempnam(sys_get_temp_dir(), 'hns_');
            $pdf->Output('F', $tmp, true);
            $upload = new UploadedFile($tmp, "doc_{$i}.pdf", 'application/pdf', null, true);

            $fixture = new Pdf();
            $fixture->setFile($upload);
            $fixture->setPublic(0 === $i % 2);
            $fixture->setOriginalName("doc_{$i}.pdf");

            $fixture->setDescription("<p>Description {$i}</p>");
            $fixture->setEntity('stdClass:' . $i);
            $em->persist($fixture);
            $this->setReference('pdf.' . $i, $fixture);
        }
        $em->flush();
    }
}
