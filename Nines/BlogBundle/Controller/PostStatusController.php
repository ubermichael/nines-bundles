<?php

namespace Nines\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\BlogBundle\Entity\PostStatus;
use Nines\BlogBundle\Form\PostStatusType;

/**
 * PostStatus controller.
 *
 * @Route("/post_status")
 */
class PostStatusController extends Controller
{
    /**
     * Lists all PostStatus entities.
     *
     * @Route("/", name="post_status_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesBlogBundle:PostStatus e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $postStatuses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'postStatuses' => $postStatuses,
        );
    }
    /**
     * Search for PostStatus entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesBlogBundle:PostStatus repository. Replace the fieldName with
	 * something appropriate, and adjust the generated search.html.twig
	 * template.
	 * 
     //    public function searchQuery($q) {
     //        $qb = $this->createQueryBuilder('e');
     //        $qb->where("e.fieldName like '%$q%'");
     //        return $qb->getQuery();
     //    }
	 *
     *
     * @Route("/search", name="post_status_search")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesBlogBundle:PostStatus');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->searchQuery($q);
			$paginator = $this->get('knp_paginator');
			$postStatuses = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$postStatuses = array();
		}

        return array(
            'postStatuses' => $postStatuses,
			'q' => $q,
        );
    }
    /**
     * Full text search for PostStatus entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesBlogBundle:PostStatus repository. Replace the fieldName with
	 * something appropriate, and adjust the generated fulltext.html.twig
	 * template.
	 * 
	//    public function fulltextQuery($q) {
	//        $qb = $this->createQueryBuilder('e');
	//        $qb->addSelect("MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') as score");
	//        $qb->add('where', "MATCH_AGAINST (e.name, :q 'IN BOOLEAN MODE') > 0.5");
	//        $qb->orderBy('score', 'desc');
	//        $qb->setParameter('q', $q);
	//        return $qb->getQuery();
	//    }	 
	 * 
	 * Requires a MatchAgainst function be added to doctrine, and appropriate
	 * fulltext indexes on your PostStatus entity.
	 *     ORM\Index(name="alias_name_idx",columns="name", flags={"fulltext"})
	 *
     *
     * @Route("/fulltext", name="post_status_fulltext")
     * @Method("GET")
     * @Template()
	 * @param Request $request
	 * @return array
     */
    public function fulltextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesBlogBundle:PostStatus');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q);
			$paginator = $this->get('knp_paginator');
			$postStatuses = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$postStatuses = array();
		}

        return array(
            'postStatuses' => $postStatuses,
			'q' => $q,
        );
    }

    /**
     * Creates a new PostStatus entity.
     *
     * @Route("/new", name="post_status_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        $postStatus = new PostStatus();
        $form = $this->createForm('Nines\BlogBundle\Form\PostStatusType', $postStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($postStatus);
            $em->flush();

            $this->addFlash('success', 'The new postStatus was created.');
            return $this->redirectToRoute('post_status_show', array('id' => $postStatus->getId()));
        }

        return array(
            'postStatus' => $postStatus,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a PostStatus entity.
     *
     * @Route("/{id}", name="post_status_show")
     * @Method("GET")
     * @Template()
	 * @param PostStatus $postStatus
     */
    public function showAction(PostStatus $postStatus)
    {

        return array(
            'postStatus' => $postStatus,
        );
    }

    /**
     * Displays a form to edit an existing PostStatus entity.
     *
     * @Route("/{id}/edit", name="post_status_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param PostStatus $postStatus
     */
    public function editAction(Request $request, PostStatus $postStatus)
    {
        $editForm = $this->createForm('Nines\BlogBundle\Form\PostStatusType', $postStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postStatus has been updated.');
            return $this->redirectToRoute('post_status_show', array('id' => $postStatus->getId()));
        }

        return array(
            'postStatus' => $postStatus,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a PostStatus entity.
     *
     * @Route("/{id}/delete", name="post_status_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param PostStatus $postStatus
     */
    public function deleteAction(Request $request, PostStatus $postStatus)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postStatus);
        $em->flush();
        $this->addFlash('success', 'The postStatus was deleted.');

        return $this->redirectToRoute('post_status_index');
    }
}
