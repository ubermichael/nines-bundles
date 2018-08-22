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
class ElementController extends Controller
{
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
    public function indexAction(Request $request)
    {
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
     * Typeahead API endpoint for Element entities.
     *
     * To make this work, add something like this to ElementRepository:
        //    public function typeaheadQuery($q) {
        //        $qb = $this->createQueryBuilder('e');
        //        $qb->andWhere("e.name LIKE :q");
        //        $qb->orderBy('e.name');
        //        $qb->setParameter('q', "{$q}%");
        //        return $qb->getQuery()->execute();
        //    }
     *
     * @param Request $request
     *
     * @Route("/typeahead", name="element_typeahead")
     * @Method("GET")
     * @return JsonResponse
     */
    public function typeahead(Request $request)
    {
        $q = $request->query->get('q');
        if( ! $q) {
            return new JsonResponse([]);
        }
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository(Element::class);
        $data = [];
        foreach($repo->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }
        return new JsonResponse($data);
    }
    /**
     * Search for Element entities.
     *
     * To make this work, add a method like this one to the
     * NinesDublinCoreBundle:Element repository. Replace the fieldName with
     * something appropriate, and adjust the generated search.html.twig
     * template.
     *
     * <code><pre>
     *    public function searchQuery($q) {
     *       $qb = $this->createQueryBuilder('e');
     *       $qb->addSelect("MATCH (e.title) AGAINST(:q BOOLEAN) as HIDDEN score");
     *       $qb->orderBy('score', 'DESC');
     *       $qb->setParameter('q', $q);
     *       return $qb->getQuery();
     *    }
     * </pre></code>
     *
     * @param Request $request
     *
     * @Route("/search", name="element_search")
     * @Method("GET")
     * @Template()
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
     * Creates a new Element entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new", name="element_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
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
     * Creates a new Element entity in a popup.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new_popup", name="element_new_popup")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newPopupAction(Request $request)
    {
        return $this->newAction($request);
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
    public function showAction(Element $element)
    {

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
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/edit", name="element_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Element $element)
    {
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
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/delete", name="element_delete")
     * @Method("GET")
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
