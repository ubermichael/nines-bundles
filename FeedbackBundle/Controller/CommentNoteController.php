<?php

namespace Nines\FeedbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\FeedbackBundle\Form\CommentNoteType;

/**
 * CommentNote controller.
 *
 * @IsGranted("ROLE_USER")
 * @Route("/admin/comment_note")
 */
class CommentNoteController extends Controller
{
    /**
     * Lists all CommentNote entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="admin_comment_note_index", methods={"GET"})

     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(CommentNote::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $commentNotes = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'commentNotes' => $commentNotes,
        );
    }

    /**
     * Creates a new CommentNote entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/new", name="admin_comment_note_new", methods={"GET","POST"})

     * @Template()
     */
    public function newAction(Request $request)
    {
        $commentNote = new CommentNote();
        $form = $this->createForm(CommentNoteType::class, $commentNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentNote);
            $em->flush();

            $this->addFlash('success', 'The new commentNote was created.');
            return $this->redirectToRoute('admin_comment_note_show', array('id' => $commentNote->getId()));
        }

        return array(
            'commentNote' => $commentNote,
            'form' => $form->createView(),
        );
    }

}
