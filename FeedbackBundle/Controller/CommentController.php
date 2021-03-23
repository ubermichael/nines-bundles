<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Controller;

use Exception;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\FeedbackBundle\Entity\Comment;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\AdminCommentType;
use Nines\FeedbackBundle\Form\CommentNoteType;
use Nines\FeedbackBundle\Form\CommentType;
use Nines\FeedbackBundle\Services\CommentService;
use Nines\FeedbackBundle\Services\NotifierService;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Comment controller.
 *
 * @Route("/comment")
 */
class CommentController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * Lists all Comment entities.
     *
     * @return array
     *
     * @Route("/", name="nines_feedback_comment_index", methods={"GET"})
     * @IsGranted("ROLE_COMMENT_ADMIN")
     *
     * @Template
     */
    public function indexAction(Request $request, CommentService $service) {
        $em = $this->getDoctrine()->getManager();
        $statusRepo = $em->getRepository(CommentStatus::class);
        $commentRepo = $em->getRepository(Comment::class);
        $qb = $commentRepo->createQueryBuilder('e');

        $statusName = $request->query->get('status');
        if ($statusName) {
            $status = $statusRepo->findOneBy([
                'name' => $statusName,
            ]);
            $qb->andWhere('e.status = :status');
            $qb->setParameter('status', $status);
        }
        $qb->orderBy('e.id');
        $query = $qb->getQuery();

        $comments = $this->paginator->paginate($query, $request->query->getInt('page', 1), 25);

        return [
            'comments' => $comments,
            'service' => $service,
            'statuses' => $statusRepo->findAll(),
        ];
    }

    /**
     * Full text search for Comment entities.
     *
     * @Route("/fulltext", name="nines_feedback_comment_fulltext", methods={"GET"})
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     * @Template
     *
     * @return array
     */
    public function fulltextAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_COMMENT_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Comment::class);
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q);

            $comments = $this->paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $comments = [];
        }

        return [
            'comments' => $comments,
            'q' => $q,
        ];
    }

    /**
     * Post a comment on an entity.
     *
     * @Route("/post", name="nines_feedback_comment_post", methods={"POST"})
     *
     * @Template
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     *
     * @return array|RedirectResponse
     */
    public function postAction(Request $request, CommentService $service, NotifierService $notifier) {
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('entity_id', null);
        $class = $request->request->get('entity_class', null);

        if ( ! $service->acceptsComments($class)) {
            throw new Exception('Cannot accept comments for this class.');
        }
        $repo = $em->getRepository($class);
        $entity = $repo->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->addComment($entity, $comment);
            $notifier->newComment($comment);
            $this->addFlash('success', 'Thank you for your suggestion.');

            return $this->redirect($service->entityUrl($comment));
        }

        return [
            'entity' => $entity,
            'service' => $service,
        ];
    }

    /**
     * Finds and displays a Comment entity.
     *
     * @Route("/{id}", name="nines_feedback_comment_show", methods={"GET", "POST"})
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     * @Template
     *
     * @return array|RedirectResponse
     */
    public function showAction(Request $request, Comment $comment, CommentService $service) {
        $this->denyAccessUnlessGranted('ROLE_COMMENT_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $statusForm = $this->createForm(AdminCommentType::class, $comment);
        $statusForm->handleRequest($request);
        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The comment was updated.');

            return $this->redirect($this->generateUrl('nines_feedback_comment_show', [
                'id' => $comment->getId(),
            ]));
        }

        $commentNote = new CommentNote();
        $commentNote->setComment($comment);
        $commentNote->setUser($this->getUser());
        $noteForm = $this->createForm(CommentNoteType::class, $commentNote);
        $noteForm->handleRequest($request);
        if ($noteForm->isSubmitted() && $noteForm->isValid()) {
            $comment->addNote($commentNote);
            $em->persist($commentNote);
            $em->flush();
            $this->addFlash('success', 'The comment note was added.');

            return $this->redirect($this->generateUrl('nines_feedback_comment_show', [
                'id' => $comment->getId(),
            ]));
        }

        return [
            'comment' => $comment,
            'service' => $service,
            'statuses' => $em->getRepository(CommentStatus::class)->findAll(),
            'statusForm' => $statusForm->createView(),
            'noteForm' => $noteForm->createView(),
        ];
    }

    /**
     * Deletes a Comment entity.
     *
     * @Route("/{id}/delete", name="nines_feedback_comment_delete", methods={"GET"})
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, Comment $comment) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'The comment was deleted.');

        if ($request->query->has('ref')) {
            return $this->redirect($request->query->get('ref'));
        }

        return $this->redirectToRoute('nines_feedback_comment_index');
    }
}
