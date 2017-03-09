<?php

namespace Nines\DublinCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Element;
use AppBundle\Form\ElementType;

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
        $dql = 'SELECT e FROM AppBundle:Element e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $elements = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'elements' => $elements,
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
        $form = $this->createForm('AppBundle\Form\ElementType', $element);
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
        $editForm = $this->createForm('AppBundle\Form\ElementType', $element);
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
