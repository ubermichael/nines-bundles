<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentStatusType;
use Nines\FeedbackBundle\Repository\CommentStatusRepository;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment_status")
 */
class CommentStatusController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_feedback_comment_status_index", methods={"GET"})
     */
    public function index(Request $request, CommentStatusRepository $commentStatusRepository) : Response {
        $query = $commentStatusRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesFeedback/comment_status/index.html.twig', [
            'comment_statuses' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/new", name="nines_feedback_comment_status_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_FEEDBACK_ADMIN")
     */
    public function new(Request $request) : Response {
        $commentStatus = new CommentStatus();
        $form = $this->createForm(CommentStatusType::class, $commentStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentStatus);
            $entityManager->flush();
            $this->addFlash('success', 'The new commentStatus has been saved.');

            return $this->redirectToRoute('nines_feedback_comment_status_show', ['id' => $commentStatus->getId()]);
        }

        return $this->render('@NinesFeedback/comment_status/new.html.twig', [
            'comment_status' => $commentStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="nines_feedback_comment_status_show", methods={"GET"})
     */
    public function show(CommentStatus $commentStatus) : Response {
        return $this->render('@NinesFeedback/comment_status/show.html.twig', [
            'comment_status' => $commentStatus,
        ]);
    }

    /**
     * @IsGranted("ROLE_FEEDBACK_ADMIN")
     * @Route("/{id}/edit", name="nines_feedback_comment_status_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, CommentStatus $commentStatus) : Response {
        $form = $this->createForm(CommentStatusType::class, $commentStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated commentStatus has been saved.');

            return $this->redirectToRoute('nines_feedback_comment_status_show', ['id' => $commentStatus->getId()]);
        }

        return $this->render('@NinesFeedback/comment_status/edit.html.twig', [
            'comment_status' => $commentStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_FEEDBACK_ADMIN")
     * @Route("/{id}", name="nines_feedback_comment_status_delete", methods={"DELETE"})
     */
    public function delete(Request $request, CommentStatus $commentStatus) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $commentStatus->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($commentStatus);
            $entityManager->flush();
            $this->addFlash('success', 'The commentStatus has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('nines_feedback_comment_status_index');
    }
}
