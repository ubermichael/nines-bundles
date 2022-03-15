<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\MediaBundle\Service\AudioManager;
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
 * @Route("/audio")
 */
class AudioController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_media_audio_index", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function index(Request $request, AudioRepository $audioRepository) : Response {
        $query = $audioRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesMedia/audio/index.html.twig', [
            'audios' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/search", name="nines_media_audio_search", methods={"GET"})
     *
     * @IsGranted("ROLE_MEDIA_ADMIN")
     * @Template
     */
    public function search(Request $request, AudioRepository $audioRepository) : Response {
        $q = $request->query->get('q');
        if ($q) {
            $query = $audioRepository->searchQuery($q);
            $audios = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $audios = [];
        }

        return $this->render('@NinesMedia/audio/search.html.twig', [
            'audios' => $audios,
            'q' => $q,
        ]);
    }

    /**
     * @Route("/{id}", name="nines_media_audio_show", methods={"GET"})
     * @Template
     * @IsGranted("ROLE_MEDIA_ADMIN")
     */
    public function show(Audio $audio, AudioManager $manager) : Response {
        return $this->render('@NinesMedia/audio/show.html.twig', [
            'audio' => $audio,
            'manager' => $manager,
        ]);
    }

    /**
     * @Route("/{id}/play", name="nines_media_audio_play", methods={"GET"})
     */
    public function play(Audio $audio) : BinaryFileResponse {
        if ( ! $audio->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($audio->getFile());
    }
}
