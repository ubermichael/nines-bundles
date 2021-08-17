<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Template
     */
    public function index(Request $request, PdfRepository $pdfRepository) : array {
        $query = $pdfRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'pdfs' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="nines_media_pdf_search", methods={"GET"})
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Template
     *
     * @return array
     */
    public function search(Request $request, PdfRepository $pdfRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $pdfRepository->searchQuery($q);
            $pdfs = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $pdfs = [];
        }

        return [
            'pdfs' => $pdfs,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="nines_media_pdf_typeahead", methods={"GET"})
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, PdfRepository $pdfRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($pdfRepository->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}", name="nines_media_pdf_show", methods={"GET"})
     * @Template
     *
     * @return array
     */
    public function show(Pdf $pdf, PdfManager $manager) {
        return [
            'pdf' => $pdf,
            'manager' => $manager,
        ];
    }

    /**
     * @Route("/{id}/view", name="nines_media_pdf_view", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function view(Pdf $pdf) {
        if ( ! $pdf->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($pdf->getFile());
    }

    /**
     * @Route("/{id}/thumb", name="nines_media_pdf_thumb", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function thumbnail(Pdf $pdf) {
        if ( ! $pdf->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($pdf->getThumbFile());
    }

}
