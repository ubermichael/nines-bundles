<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use App\Entity\Person;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\MediaBundle\Service\ImageManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 * @IsGranted("ROLE_CONTENT_ADMIN")
 */
class ImageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_image_index", methods={"GET"})
     *
     * @Template
     */
    public function index(Request $request, ImageRepository $imageRepository) : array {
        $query = $imageRepository->indexQuery();
        $pageSize = (int)$this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'images' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="nines_media_image_search", methods={"GET"})
     *
     * @Template
     *
     * @return array
     */
    public function search(Request $request, ImageRepository $imageRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $imageRepository->searchQuery($q);
            $images = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $images = [];
        }

        return [
            'images' => $images,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="nines_media_image_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, ImageRepository $imageRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($imageRepository->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}", name="nines_media_image_show", methods={"GET"})
     * @Template
     *
     * @return array
     */
    public function show(Image $image, ImageManager $manager) {
        return [
            'image' => $image,
            'manager' => $manager,
        ];
    }

    /**
     * @Route("/{id}/view", name="nines_media_image_view", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function view(Image $image) {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getImageFile());
    }

    /**
     * @Route("/{id}/thumb", name="nines_media_image_thumb", methods={"GET"})
     *
     * @return BinaryFileResponse
     */
    public function thumbnail(Image $image) {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getThumbFile());
    }

    /**
     * @Route("/{id}", name="nines_media_image_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Image $image) {
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
            $this->addFlash('success', 'The image has been deleted.');
        }

        return $this->redirectToRoute('nines_media_image_index');
    }
}
