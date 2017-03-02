<?php

namespace Nines\FeedbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentStatusType;

/**
 * Administrative interface for comments.
 * 
 * @Route("/admin/comment_status")
 */
class CommentStatusController extends Controller
{
    /**
     * Lists all CommentStatus entities.
     *
     * @Route("/", name="admin_comment_status_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesFeedbackBundle:CommentStatus e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $commentStatuses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'commentStatuses' => $commentStatuses,
        );
    }

    /**
     * Creates a new CommentStatus entity.
     *
     * @Route("/new", name="admin_comment_status_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $commentStatus = new CommentStatus();
        $form = $this->createForm('Nines\FeedbackBundle\Form\CommentStatusType', $commentStatus);
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
     * @Route("/{id}", name="admin_comment_status_show")
     * @Method("GET")
     * @Template()
	 * @param CommentStatus $commentStatus
     */
    public function showAction(CommentStatus $commentStatus)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return array(
            'commentStatus' => $commentStatus,
        );
    }

    /**
     * Displays a form to edit an existing CommentStatus entity.
     *
     * @Route("/{id}/edit", name="admin_comment_status_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param CommentStatus $commentStatus
     */
    public function editAction(Request $request, CommentStatus $commentStatus)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $editForm = $this->createForm('Nines\FeedbackBundle\Form\CommentStatusType', $commentStatus);
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
     * @Route("/{id}/delete", name="admin_comment_status_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param CommentStatus $commentStatus
     */
    public function deleteAction(Request $request, CommentStatus $commentStatus)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($commentStatus);
        $em->flush();
        $this->addFlash('success', 'The commentStatus was deleted.');

        return $this->redirectToRoute('admin_comment_status_index');
    }
}
