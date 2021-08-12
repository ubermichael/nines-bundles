<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\DublinCoreBundle\Entity\Value;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\DublinCoreBundle\Service\ValueManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/value")
 */
class ValueController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_dc_value_index", methods={"GET"})
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Template
     */
    public function index(Request $request, ValueRepository $valueRepository, ValueManager $valueManager) : array {
        $query = $valueRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'values' => $this->paginator->paginate($query, $page, $pageSize),
            'value_manager' => $valueManager,
        ];
    }

    /**
     * @Route("/search", name="nines_dc_value_search", methods={"GET"})
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Template
     */
    public function search(Request $request, ValueRepository $valueRepository, ValueManager $valueManager) : array {
        $q = $request->query->get('q');
        if ($q) {
            $query = $valueRepository->searchQuery($q);
            $values = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $values = [];
        }

        return [
            'values' => $values,
            'q' => $q,
            'value_manager' => $valueManager,
        ];
    }

    /**
     * @Route("/{id}", name="nines_dc_value_show", methods={"GET"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Template
     */
    public function show(Value $value, ValueManager $valueManager) : array {
        return [
            'value' => $value,
            'value_manager' => $valueManager,
        ];
    }
}
