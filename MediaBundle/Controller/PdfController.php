<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Repository\PdfRepository;
use Nines\MediaBundle\Service\PdfManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pdf")
 */
class PdfController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_pdf_index", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function index(Request $request, PdfRepository $pdfRepository) : Response {
        $query = $pdfRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesMedia/pdf/index.html.twig', [
            'pdfs' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="nines_media_pdf_search", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function search(Request $request, PdfRepository $pdfRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $pdfRepository->searchQuery($q);
            $pdfs = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $pdfs = [];
        }

        return $this->render('@NinesMedia/pdf/search.html.twig', [
            'pdfs' => $pdfs,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/{id}", name="nines_media_pdf_show", methods={"GET"})
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function show(Pdf $pdf, PdfManager $manager) : Response {
        return $this->render('@NinesMedia/pdf/show.html.twig', [
            'pdf' => $pdf,
            'manager' => $manager,
        ]);
    }

    /**
     * @Route("/{id}/view", name="nines_media_pdf_view", methods={"GET"})
     */
    public function view(Pdf $pdf) : BinaryFileResponse {
        if ( ! $pdf->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($pdf->getFile());
    }

    /**
     * @Route("/{id}/thumb", name="nines_media_pdf_thumb", methods={"GET"})
     */
    public function thumbnail(Pdf $pdf) : BinaryFileResponse {
        if ( ! $pdf->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($pdf->getThumbFile());
    }
}
