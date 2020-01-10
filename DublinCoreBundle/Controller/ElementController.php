<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\DublinCoreBundle\Entity\Element;
use Nines\DublinCoreBundle\Form\ElementType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Element controller.
 *
 * @Route("/element")
 * @IsGranted("ROLE_USER")
 */
class ElementController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all Element entities.
     *
     * @return array
     *
     * @Route("/", name="element_index", methods={"GET"})
     *
     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Element::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();

        $elements = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'elements' => $elements,
        ];
    }

    /**
     * Creates a new Element entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_DC_ADMIN")
     * @Route("/new", name="element_new", methods={"GET","POST"})
     *
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

            return $this->redirectToRoute('element_show', ['id' => $element->getId()]);
        }

        return [
            'element' => $element,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Element entity.
     *
     * @return array
     *
     * @Route("/{id}", name="element_show", methods={"GET"})
     *
     * @Template()
     */
    public function showAction(Element $element) {
        return [
            'element' => $element,
        ];
    }

    /**
     * Displays a form to edit an existing Element entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_DC_ADMIN")
     * @Route("/{id}/edit", name="element_edit", methods={"GET","POST"})
     *
     * @Template()
     */
    public function editAction(Request $request, Element $element) {
        $editForm = $this->createForm(ElementType::class, $element);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The element has been updated.');

            return $this->redirectToRoute('element_show', ['id' => $element->getId()]);
        }

        return [
            'element' => $element,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a Element entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_DC_ADMIN")
     * @Route("/{id}/delete", name="element_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, Element $element) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($element);
        $em->flush();
        $this->addFlash('success', 'The element was deleted.');

        return $this->redirectToRoute('element_index');
    }
}
