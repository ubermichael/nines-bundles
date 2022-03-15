<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\MediaBundle\Service\ImageManager;
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
 * @Route("/image")
 */
class ImageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_image_index", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function index(Request $request, ImageRepository $imageRepository) : Response {
        $query = $imageRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesMedia/image/index.html.twig', [
            'images' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="nines_media_image_search", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function search(Request $request, ImageRepository $imageRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $imageRepository->searchQuery($q);
            $images = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $images = [];
        }

        return $this->render('@NinesMedia/image/search.html.twig', [
            'images' => $images,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/{id}", name="nines_media_image_show", methods={"GET"})
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function show(Image $image, ImageManager $manager) : Response {
        return $this->render('@NinesMedia/image/show.html.twig', [
            'image' => $image,
            'manager' => $manager,
        ]);
    }

    /**
     * @Route("/{id}/view", name="nines_media_image_view", methods={"GET"})
     */
    public function view(Image $image) : BinaryFileResponse {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getFile());
    }

    /**
     * @Route("/{id}/thumb", name="nines_media_image_thumb", methods={"GET"})
     */
    public function thumbnail(Image $image) : BinaryFileResponse {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getThumbFile());
    }
}
