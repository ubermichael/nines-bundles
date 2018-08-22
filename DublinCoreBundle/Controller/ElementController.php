<?php

namespace Nines\DublinCoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Form\ElementType;

/**
 * Element controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/element")
 */
class ElementController extends Controller {

    /**
     * Lists all Element entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="element_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Element::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $elements = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'elements' => $elements,
        );
    }

    /**
     * Creates a new Element entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_DC_ADMIN')")
     * @Route("/new", name="element_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request) {
        $element = new Element();
        $form = $this->createForm(ElementType::class, $element);
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
     * @param Element $element
     *
     * @return array
     *
     * @Route("/{id}", name="element_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Element $element) {

        return array(
            'element' => $element,
        );
    }

    /**
     * Displays a form to edit an existing Element entity.
     *
     *
     * @param Request $request
     * @param Element $element
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_DC_ADMIN')")
     * @Route("/{id}/edit", name="element_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Element $element) {
        $editForm = $this->createForm(ElementType::class, $element);
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
     *
     * @param Request $request
     * @param Element $element
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_DC_ADMIN')")
     * @Route("/{id}/delete", name="element_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Element $element) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($element);
        $em->flush();
        $this->addFlash('success', 'The element was deleted.');

        return $this->redirectToRoute('element_index');
    }

}
