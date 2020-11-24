<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Link;
use Nines\MediaBundle\Repository\LinkRepository;
use Nines\MediaBundle\Service\LinkManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/link")
 */
class LinkController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_link_index", methods={"GET"})
     *
     * @Template
     */
    public function index(Request $request, LinkRepository $linkRepository, LinkManager $linkManager) : array {
        $query = $linkRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'links' => $this->paginator->paginate($query, $page, $pageSize),
            'link_manager' => $linkManager,
        ];
    }

    /**
     * @Route("/search", name="nines_media_link_search", methods={"GET"})
     *
     * @Template
     */
    public function search(Request $request, LinkRepository $linkRepository, LinkManager $linkManager) : array {
        $q = $request->query->get('q');
        if ($q) {
            $query = $linkRepository->searchQuery($q);
            $links = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $links = [];
        }

        return [
            'links' => $links,
            'q' => $q,
            'link_manager' => $linkManager,
        ];
    }

    /**
     * @Route("/{id}", name="nines_media_link_show", methods={"GET"})
     * @Template
     */
    public function show(Link $link, LinkManager $linkManager) : array {
        return [
            'link' => $link,
            'link_manager' => $linkManager,
        ];
    }
}
