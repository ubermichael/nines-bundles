<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentStatusType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CommentStatus controller.
 *
 * @Route("/comment_status")
 * @IsGranted("ROLE_USER")
 */
class CommentStatusController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all CommentStatus entities.
     *
     * @return array
     *
     * @Route("/", name="nines_feedback_comment_status_index", methods={"GET"})
     *
     * @Template
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(CommentStatus::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();

        $commentStatuses = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'commentStatuses' => $commentStatuses,
        ];
    }

    /**
     * Creates a new CommentStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     * @Route("/new", name="nines_feedback_comment_status_new", methods={"GET", "POST"})
     *
     * @Template
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

            return $this->redirectToRoute('nines_feedback_comment_status_show', ['id' => $commentStatus->getId()]);
        }

        return [
            'commentStatus' => $commentStatus,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a CommentStatus entity.
     *
     * @return array
     *
     * @Route("/{id}", name="nines_feedback_comment_status_show", methods={"GET"})
     *
     * @Template
     */
    public function showAction(CommentStatus $commentStatus) {
        return [
            'commentStatus' => $commentStatus,
        ];
    }

    /**
     * Displays a form to edit an existing CommentStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     * @Route("/{id}/edit", name="nines_feedback_comment_status_edit", methods={"GET", "POST"})
     *
     * @Template
     */
    public function editAction(Request $request, CommentStatus $commentStatus) {
        $editForm = $this->createForm(CommentStatusType::class, $commentStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The commentStatus has been updated.');

            return $this->redirectToRoute('nines_feedback_comment_status_show', ['id' => $commentStatus->getId()]);
        }

        return [
            'commentStatus' => $commentStatus,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a CommentStatus entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_COMMENT_ADMIN")
     * @Route("/{id}/delete", name="nines_feedback_comment_status_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, CommentStatus $commentStatus) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($commentStatus);
        $em->flush();
        $this->addFlash('success', 'The commentStatus was deleted.');

        return $this->redirectToRoute('nines_feedback_comment_status_index');
    }
}
