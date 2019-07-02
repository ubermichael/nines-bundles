<?php

namespace Nines\FeedbackBundle\Controller;

use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentStatusType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * CommentStatus controller.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/admin/comment_status")
 */
class CommentStatusController extends Controller {

    /**
     * Lists all CommentStatus entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="admin_comment_status_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(CommentStatus::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $commentStatuses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'commentStatuses' => $commentStatuses,
        );
    }

    /**
     * Creates a new CommentStatus entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Route("/new", name="admin_comment_status_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $commentStatus = new CommentStatus();
        $form = $this->createForm(CommentStatusType::class, $commentStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentStatus);
            $em->flush();

            $this->addFlash('success', 'The new commentStatus was created.');
            return $this->redirectToRoute('admin_comment_status_show', array('id' => $commentStatus->getId()));
        }

        return array(
            'commentStatus' => $commentStatus,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a CommentStatus entity.
     *
     * @param CommentStatus $commentStatus
     *
     * @return array
     *
     * @Route("/{id}", name="admin_comment_status_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(CommentStatus $commentStatus) {

        return array(
            'commentStatus' => $commentStatus,
        );
    }

    /**
     * Displays a form to edit an existing CommentStatus entity.
     *
     *
     * @param Request $request
     * @param CommentStatus $commentStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Route("/{id}/edit", name="admin_comment_status_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, CommentStatus $commentStatus) {
        $editForm = $this->createForm(CommentStatusType::class, $commentStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The commentStatus has been updated.');
            return $this->redirectToRoute('admin_comment_status_show', array('id' => $commentStatus->getId()));
        }

        return array(
            'commentStatus' => $commentStatus,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a CommentStatus entity.
     *
     *
     * @param Request $request
     * @param CommentStatus $commentStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Route("/{id}/delete", name="admin_comment_status_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, CommentStatus $commentStatus) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($commentStatus);
        $em->flush();
        $this->addFlash('success', 'The commentStatus was deleted.');

        return $this->redirectToRoute('admin_comment_status_index');
    }

}
