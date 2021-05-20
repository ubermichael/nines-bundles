<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\FeedbackBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\FeedbackBundle\Entity\CommentNote;
use Nines\FeedbackBundle\Form\CommentNoteType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * CommentNote controller.
 *
 * @Route("/comment_note")
 * @IsGranted("ROLE_USER")
 */
class CommentNoteController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all CommentNote entities.
     *
     * @return array
     *
     * @Route("/", name="nines_feedback_comment_note_index", methods={"GET"})
     *
     * @Template
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(CommentNote::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();

        $commentNotes = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'commentNotes' => $commentNotes,
        ];
    }

    /**
     * Creates a new CommentNote entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/new", name="nines_feedback_comment_note_new", methods={"GET", "POST"})
     *
     * @Template
     */
    public function newAction(Request $request) {
        $commentNote = new CommentNote();
        $form = $this->createForm(CommentNoteType::class, $commentNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentNote);
            $em->flush();

            $this->addFlash('success', 'The new commentNote was created.');

            return $this->redirectToRoute('nines_feedback_comment_note_show', ['id' => $commentNote->getId()]);
        }

        return [
            'commentNote' => $commentNote,
            'form' => $form->createView(),
        ];
    }
}
