<?php

namespace Nines\FeedbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Form\CommentType;

/**
 * Comment controller.
 *
 * @Route("/admin/comment")
 */
class CommentController extends Controller {

    /**
     * Lists all Comment entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="admin_comment_index")
     * @Security("has_role('ROLE_USER')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Comment::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $comments = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'comments' => $comments,
        );
    }

    /**
     * Search for Comment entities.
     *
     * @param Request $request
     *
     * @Route("/search", name="admin_comment_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesFeedbackBundle:Comment');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $comments = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $comments = array();
        }

        return array(
            'comments' => $comments,
            'q' => $q,
        );
    }

    /**
     * Creates a new Comment entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Route("/new", name="admin_comment_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'The new comment was created.');
            return $this->redirectToRoute('admin_comment_show', array('id' => $comment->getId()));
        }

        return array(
            'comment' => $comment,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Comment entity.
     *
     * @param Comment $comment
     *
     * @return array
     *
     * @Security("has_role('ROLE_USER')")
     * @Route("/{id}", name="admin_comment_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Comment $comment) {

        return array(
            'comment' => $comment,
        );
    }

    /**
     * Displays a form to edit an existing Comment entity.
     *
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Route("/{id}/edit", name="admin_comment_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Comment $comment) {
        $editForm = $this->createForm(CommentType::class, $comment);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The comment has been updated.');
            return $this->redirectToRoute('admin_comment_show', array('id' => $comment->getId()));
        }

        return array(
            'comment' => $comment,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Comment entity.
     *
     *
     * @param Request $request
     * @param Comment $comment
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Route("/{id}/delete", name="admin_comment_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Comment $comment) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'The comment was deleted.');

        return $this->redirectToRoute('admin_comment_index');
    }

}
