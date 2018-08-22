<?php

namespace Nines\FeedbackBundle\Controller;

use Exception;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\AdminCommentType;
use Nines\FeedbackBundle\Form\CommentNoteType;
use Nines\FeedbackBundle\Form\CommentType;
use Nines\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $statusRepo = $em->getRepository(CommentStatus::class);
        $commentRepo = $em->getRepository(Comment::class);
        $qb = $commentRepo->createQueryBuilder('e');

        $statusName = $request->query->get('status');
        if ($statusName) {
            $status = $statusRepo->findOneBy(array(
                'name' => $statusName,
            ));
            $qb->andWhere('e.status = :status');
            $qb->setParameter('status', $status);
        }
        $qb->orderBy('e.id');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $comments = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        $service = $this->get('feedback.comment');

        return array(
            'comments' => $comments,
            'service' => $service,
            'statuses' => $statusRepo->findAll(),
        );
    }

     /**
     * Full text search for Comment entities.
     *
     * @Route("/fulltext", name="admin_comment_fulltext")
     * @Method("GET")
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Template()
     * @param Request $request
     * @return array
     */
    public function fulltextAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_COMMENT_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Comment::class);
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q);
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
     * Post a comment on an entity.
     *
     * @Method("POST")
     * @Route("/post", name="comment_post")
     * @param Request $request
     * @Template()
     */
    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
		$service = $this->get('feedback.comment');
        $id = $request->request->get('entity_id', null);
        $class = $request->request->get('entity_class', null);

        if(!$service->acceptsComments($class)) {
            throw new Exception("Cannot accept comments for this class.");
        }
        $repo = $em->getRepository($class);
        $entity = $repo->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->addComment($entity, $comment);
            $this->addFlash('success', 'Thank you for your suggestion.');
            return $this->redirect($service->entityUrl($comment));
        }

        return array(
            'entity' => $entity,
			'service' => $service,
        );
    }

    /**
     * Finds and displays a Comment entity.
     *
     * @Route("/{id}", name="admin_comment_show")
     * @Method({"GET","POST"})
     * @Security("has_role('ROLE_COMMENT_ADMIN')")
     * @Template()
     * @param Request $request
     * @param Comment $comment
     */
    public function showAction(Request $request, Comment $comment, User $user) {
        $this->denyAccessUnlessGranted('ROLE_COMMENT_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $service = $this->get('feedback.comment');
        $statusForm = $this->createForm(AdminCommentType::class, $comment);
        $statusForm->handleRequest($request);
        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The comment was updated.');
            return $this->redirect($this->generateUrl('admin_comment_show', array(
                'id' => $comment->getId()
            )));
        }

        $commentNote = new CommentNote();
        $commentNote->setComment($comment);
        $commentNote->setUser($user);
        $noteForm = $this->createForm(CommentNoteType::class, $commentNote);
        $noteForm->handleRequest($request);
        if($noteForm->isSubmitted() && $noteForm->isValid()) {
            $comment->addNote($commentNote);
            $em->persist($commentNote);
            $em->flush();
            $this->addFlash('success', 'The comment note was added.');
            return $this->redirect($this->generateUrl('admin_comment_show', array(
                'id' => $comment->getId()
            )));
        }

        return array(
            'comment' => $comment,
            'service' => $service,
            'statuses' => $em->getRepository(CommentStatus::class)->findAll(),
            'statusForm' => $statusForm->createView(),
            'noteForm' => $noteForm->createView(),
        );
    }

    /**
     * Deletes a Comment entity.
     *
     * @Route("/{id}/delete", name="admin_comment_delete")
     * @Method("GET")
     * @Security("is_granted('ROLE_COMMENT_ADMIN')")
     * @param Request $request
     * @param Comment $comment
     */
    public function deleteAction(Request $request, Comment $comment) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'The comment was deleted.');

        if ($request->query->has('ref')) {
            return $this->redirect($request->query->get('ref'));
        }

        return $this->redirectToRoute('admin_comment_index');
    }

}
