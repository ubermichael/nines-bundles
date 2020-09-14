<?php

namespace Nines\MediaBundle\Controller;

use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Form\ImageType;
use Nines\MediaBundle\Repository\ImageRepository;

use Nines\MediaBundle\Services\ImageManager;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/image")
 */
class ImageController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="image_index", methods={"GET"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, ImageRepository $imageRepository) : array
    {
        $query = $imageRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'images' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="image_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, ImageRepository $imageRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $imageRepository->searchQuery($q);
            $images = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $images = [];
        }

        return [
            'images' => $images,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="image_typeahead", methods={"GET"})
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
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/{id}", name="image_show", methods={"GET"})
     * @Template()
     * @param Image $image
     *
     * @param ImageManager $manager
     *
     * @return array
     */
    public function show(Image $image, ImageManager $manager) {
        return [
            'image' => $image,
            'manager' => $manager
        ];
    }

    /**
     * @Route("/{id}/view", name="image_view", methods={"GET"})
     * @param Image $image
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
     * @Route("/{id}/thumb", name="image_thumb", methods={"GET"})
     * @param Image $image
     *
     * @return BinaryFileResponse
     */
    public function thumbnail(Image $image) {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getThumbFile());
    }

}
