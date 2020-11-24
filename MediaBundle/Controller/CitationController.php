<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Citation;
use Nines\MediaBundle\Repository\CitationRepository;
use Nines\MediaBundle\Service\CitationManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/citation")
 */
class CitationController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_citation_index", methods={"GET"})
     *
     * @Template
     */
    public function index(Request $request, CitationRepository $citationRepository, CitationManager $citationManager) : array {
        $query = $citationRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'citations' => $this->paginator->paginate($query, $page, $pageSize),
            'citation_manager' => $citationManager,
        ];
    }

    /**
     * @Route("/search", name="nines_media_citation_search", methods={"GET"})
     *
     * @Template
     */
    public function search(Request $request, CitationRepository $citationRepository, CitationManager $citationManager) : array {
        $q = $request->query->get('q');
        if ($q) {
            $query = $citationRepository->searchQuery($q);
            $citations = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $citations = [];
        }

        return [
            'citations' => $citations,
            'q' => $q,
            'Citation_manager' => $citationManager,
        ];
    }

    /**
     * @Route("/{id}", name="nines_media_citation_show", methods={"GET"})
     * @Template
     */
    public function show(Citation $citation, CitationManager $citationManager) : array {
        return [
            'citation' => $citation,
            'citation_manager' => $citationManager,
        ];
    }
}
