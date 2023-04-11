<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\DublinCoreBundle\Entity\Value;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/value")
 */
class ValueController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_dc_value_index", methods={"GET"})
     */
    public function index(Request $request, ValueRepository $valueRepository) : Response {
        $query = $valueRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesDublinCore/value/index.html.twig', [
            'values' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="nines_dc_value_search", methods={"GET"})
     */
    public function search(Request $request, ValueRepository $valueRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $valueRepository->searchQuery($q);
            $values = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), [
                'wrap-queries' => true,
            ]);
        } else {
            $values = [];
        }

        return $this->render('@NinesDublinCore/value/search.html.twig', [
            'values' => $values,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/{id}", name="nines_dc_value_show", methods={"GET"})
     */
    public function show(Value $value) : Response {
        return $this->render('@NinesDublinCore/value/show.html.twig', [
            'value' => $value,
        ]);
    }
}
