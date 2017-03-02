<?php

namespace Nines\FeedbackBundle\Controller;

use Nines\FeedbackBundle\Entity\CommentNote;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * CommentNote controller.
 *
 * @Route("/admin/comment_note")
 */
class CommentNoteController extends Controller
{
    /**
     * Lists all CommentNote entities.
     *
     * @Route("/", name="admin_comment_note_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesFeedbackBundle:CommentNote e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $commentNotes = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'commentNotes' => $commentNotes,
        );
    }

    /**
     * Full text search for CommentNote entities.
     *
     * @Route("/fulltext", name="admin_comment_note_fulltext")
     * @Method("GET")
     * @Template()
	 * @param Request $request
	 * @return array
     */
    public function fulltextAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository(CommentNote::class);
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q);
			$paginator = $this->get('knp_paginator');
			$commentNotes = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$commentNotes = array();
		}

        return array(
            'commentNotes' => $commentNotes,
			'q' => $q,
        );
    }

}
