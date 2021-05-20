<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\PostStatus;
use Nines\BlogBundle\Form\PostStatusType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PostStatus controller.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/post_status")
 */
class PostStatusController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all PostStatus entities.
     *
     * @return array
     *
     * @Route("/", name="nines_blog_post_status_index", methods={"GET"})
     *
     * @Template
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(PostStatus::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();

        $postStatuses = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'postStatuses' => $postStatuses,
        ];
    }

    /**
     * Typeahead API endpoint for PostStatus entities.
     *
     * @Route("/typeahead", name="nines_blog_post_status_typeahead", methods={"GET"})
     * @IsGranted("ROLE_BLOG_ADMIN")
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(PostStatus::class);
        $data = [];

        foreach ($repo->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Creates a new PostStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new", name="nines_blog_post_status_new", methods={"GET", "POST"})
     *
     * @Template
     */
    public function newAction(Request $request) {
        $postStatus = new PostStatus();
        $form = $this->createForm(PostStatusType::class, $postStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($postStatus);
            $em->flush();

            $this->addFlash('success', 'The new postStatus was created.');

            return $this->redirectToRoute('nines_blog_post_status_show', ['id' => $postStatus->getId()]);
        }

        return [
            'postStatus' => $postStatus,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new PostStatus entity in a popup.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new_popup", name="nines_blog_post_status_new_popup", methods={"GET", "POST"})
     *
     * @Template
     */
    public function newPopupAction(Request $request) {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a PostStatus entity.
     *
     * @return array
     *
     * @Route("/{id}", name="nines_blog_post_status_show", methods={"GET"})
     *
     * @Template
     */
    public function showAction(PostStatus $postStatus) {
        return [
            'postStatus' => $postStatus,
        ];
    }

    /**
     * Displays a form to edit an existing PostStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_post_status_edit", methods={"GET", "POST"})
     *
     * @Template
     */
    public function editAction(Request $request, PostStatus $postStatus) {
        $editForm = $this->createForm(PostStatusType::class, $postStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postStatus has been updated.');

            return $this->redirectToRoute('nines_blog_post_status_show', ['id' => $postStatus->getId()]);
        }

        return [
            'postStatus' => $postStatus,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a PostStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/delete", name="nines_blog_post_status_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, PostStatus $postStatus) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postStatus);
        $em->flush();
        $this->addFlash('success', 'The postStatus was deleted.');

        return $this->redirectToRoute('nines_blog_post_status_index');
    }
}
