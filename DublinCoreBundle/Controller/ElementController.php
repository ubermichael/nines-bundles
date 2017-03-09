<?php

namespace Nines\DublinCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Form\ElementType;

/**
 * Element controller.
 *
 * @Route("/element")
 */
class ElementController extends Controller
{
    /**
     * Lists all Element entities.
     *
     * @Route("/", name="element_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesDublinCoreBundle:Element e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $elements = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'elements' => $elements,
        );
    }
    /**
     * Search for Element entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesDublinCoreBundle:Element repository. Replace the fieldName with
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
     * @Route("/search", name="element_search")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesDublinCoreBundle:Element');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->searchQuery($q);
			$paginator = $this->get('knp_paginator');
			$elements = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$elements = array();
		}

        return array(
            'elements' => $elements,
			'q' => $q,
        );
    }
    /**
     * Full text search for Element entities.
	 *
	 * To make this work, add a method like this one to the 
	 * NinesDublinCoreBundle:Element repository. Replace the fieldName with
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
	 * fulltext indexes on your Element entity.
	 *     ORM\Index(name="alias_name_idx",columns="name", flags={"fulltext"})
	 *
     *
     * @Route("/fulltext", name="element_fulltext")
     * @Method("GET")
     * @Template()
	 * @param Request $request
	 * @return array
     */
    public function fulltextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesDublinCoreBundle:Element');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q);
			$paginator = $this->get('knp_paginator');
			$elements = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$elements = array();
		}

        return array(
            'elements' => $elements,
			'q' => $q,
        );
    }

    /**
     * Creates a new Element entity.
     *
     * @Route("/new", name="element_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        $element = new Element();
        $form = $this->createForm('Nines\DublinCoreBundle\Form\ElementType', $element);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($element);
            $em->flush();

            $this->addFlash('success', 'The new element was created.');
            return $this->redirectToRoute('element_show', array('id' => $element->getId()));
        }

        return array(
            'element' => $element,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Element entity.
     *
     * @Route("/{id}", name="element_show")
     * @Method("GET")
     * @Template()
	 * @param Element $element
     */
    public function showAction(Element $element)
    {

        return array(
            'element' => $element,
        );
    }

    /**
     * Displays a form to edit an existing Element entity.
     *
     * @Route("/{id}/edit", name="element_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param Element $element
     */
    public function editAction(Request $request, Element $element)
    {
        $editForm = $this->createForm('Nines\DublinCoreBundle\Form\ElementType', $element);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The element has been updated.');
            return $this->redirectToRoute('element_show', array('id' => $element->getId()));
        }

        return array(
            'element' => $element,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Element entity.
     *
     * @Route("/{id}/delete", name="element_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param Element $element
     */
    public function deleteAction(Request $request, Element $element)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($element);
        $em->flush();
        $this->addFlash('success', 'The element was deleted.');

        return $this->redirectToRoute('element_index');
    }
}
