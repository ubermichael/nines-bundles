<?php

namespace Nines\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\BlogBundle\Entity\PostCategory;
use Nines\BlogBundle\Form\PostCategoryType;

/**
 * PostCategory controller.
 *
 * @Route("/post_category")
 */
class PostCategoryController extends Controller
{
    /**
     * Lists all PostCategory entities.
     *
     * @Route("/", name="post_category_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesBlogBundle:PostCategory e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $postCategories = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'postCategories' => $postCategories,
        );
    }
    /**
     * Search for PostCategory entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesBlogBundle:PostCategory repository. Replace the fieldName with
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
     * @Route("/search", name="post_category_search")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesBlogBundle:PostCategory');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->searchQuery($q);
			$paginator = $this->get('knp_paginator');
			$postCategories = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$postCategories = array();
		}

        return array(
            'postCategories' => $postCategories,
			'q' => $q,
        );
    }
    /**
     * Full text search for PostCategory entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesBlogBundle:PostCategory repository. Replace the fieldName with
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
	 * fulltext indexes on your PostCategory entity.
	 *     ORM\Index(name="alias_name_idx",columns="name", flags={"fulltext"})
	 *
     *
     * @Route("/fulltext", name="post_category_fulltext")
     * @Method("GET")
     * @Template()
	 * @param Request $request
	 * @return array
     */
    public function fulltextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesBlogBundle:PostCategory');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q);
			$paginator = $this->get('knp_paginator');
			$postCategories = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$postCategories = array();
		}

        return array(
            'postCategories' => $postCategories,
			'q' => $q,
        );
    }

    /**
     * Creates a new PostCategory entity.
     *
     * @Route("/new", name="post_category_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        $postCategory = new PostCategory();
        $form = $this->createForm('Nines\BlogBundle\Form\PostCategoryType', $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($postCategory);
            $em->flush();

            $this->addFlash('success', 'The new postCategory was created.');
            return $this->redirectToRoute('post_category_show', array('id' => $postCategory->getId()));
        }

        return array(
            'postCategory' => $postCategory,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a PostCategory entity.
     *
     * @Route("/{id}", name="post_category_show")
     * @Method("GET")
     * @Template()
	 * @param PostCategory $postCategory
     */
    public function showAction(PostCategory $postCategory)
    {

        return array(
            'postCategory' => $postCategory,
        );
    }

    /**
     * Displays a form to edit an existing PostCategory entity.
     *
     * @Route("/{id}/edit", name="post_category_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param PostCategory $postCategory
     */
    public function editAction(Request $request, PostCategory $postCategory)
    {
        $editForm = $this->createForm('Nines\BlogBundle\Form\PostCategoryType', $postCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postCategory has been updated.');
            return $this->redirectToRoute('post_category_show', array('id' => $postCategory->getId()));
        }

        return array(
            'postCategory' => $postCategory,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a PostCategory entity.
     *
     * @Route("/{id}/delete", name="post_category_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param PostCategory $postCategory
     */
    public function deleteAction(Request $request, PostCategory $postCategory)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postCategory);
        $em->flush();
        $this->addFlash('success', 'The postCategory was deleted.');

        return $this->redirectToRoute('post_category_index');
    }
}
