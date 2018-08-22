<?php

namespace Nines\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\BlogBundle\Entity\PostStatus;
use Nines\BlogBundle\Form\PostStatusType;

/**
 * PostStatus controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/post_status")
 */
class PostStatusController extends Controller {

    /**
     * Lists all PostStatus entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="post_status_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(PostStatus::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $postStatuses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'postStatuses' => $postStatuses,
        );
    }

    /**
     * Typeahead API endpoint for PostStatus entities.
     *
     * @param Request $request
     *
     * @Route("/typeahead", name="post_status_typeahead")
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Method("GET")
     * @return JsonResponse
     */
    public function typeahead(Request $request) {
        $q = $request->query->get('q');
        if (!$q) {
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
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new", name="post_status_new")
     * @Method({"GET", "POST"})
     * @Template()
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
            return $this->redirectToRoute('post_status_show', array('id' => $postStatus->getId()));
        }

        return array(
            'postStatus' => $postStatus,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new PostStatus entity in a popup.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new_popup", name="post_status_new_popup")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newPopupAction(Request $request) {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a PostStatus entity.
     *
     * @param PostStatus $postStatus
     *
     * @return array
     *
     * @Route("/{id}", name="post_status_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(PostStatus $postStatus) {

        return array(
            'postStatus' => $postStatus,
        );
    }

    /**
     * Displays a form to edit an existing PostStatus entity.
     *
     *
     * @param Request $request
     * @param PostStatus $postStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/edit", name="post_status_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, PostStatus $postStatus) {
        $editForm = $this->createForm(PostStatusType::class, $postStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postStatus has been updated.');
            return $this->redirectToRoute('post_status_show', array('id' => $postStatus->getId()));
        }

        return array(
            'postStatus' => $postStatus,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a PostStatus entity.
     *
     *
     * @param Request $request
     * @param PostStatus $postStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/delete", name="post_status_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, PostStatus $postStatus) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postStatus);
        $em->flush();
        $this->addFlash('success', 'The postStatus was deleted.');

        return $this->redirectToRoute('post_status_index');
    }

}
