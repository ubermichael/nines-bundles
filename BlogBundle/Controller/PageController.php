<?php

namespace Nines\BlogBundle\Controller;

use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Form\PageType;
use Nines\UtilBundle\Services\Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends Controller {

    /**
     * Lists all Page entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="page_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Page::class);
        $query = $repo->listQuery($checker->isGranted('ROLE_USER'));
        $paginator = $this->get('knp_paginator');
        $pages = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'pages' => $pages,
        );
    }

    /**
     * @Route("/sort", name="page_sort")
     * @Method({"GET","POST"})
     * @Template()
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     *
     * @param Request $request
     * @return array
     */
    public function sortAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Page');

        if ($request->getMethod() === 'POST') {
            $order = $request->request->get('order');
            $list = explode(',', $order);
            for ($i = 0; $i < count($list); ++$i) {
                $page = $repo->find($list[$i]);
                $page->setWeight($i + 1);
                $em->flush($page);
            }
            $this->addFlash('success', 'The pages have been ordered.');
            return $this->redirect($this->generateUrl('page_sort'));
        }

        $pages = $repo->findBy(
            array('public' => true), array('weight' => 'ASC', 'title' => 'ASC')
        );
        return array(
            'pages' => $pages
        );
    }

    /**
     * Full text search for Page entities.
     *
     * @Route("/search", name="page_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Page');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q, $checker->isGranted('ROLE_USER'));
            $paginator = $this->get('knp_paginator');
            $pages = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $pages = array();
        }

        return array(
            'pages' => $pages,
            'q' => $q,
        );
    }

    /**
     * Creates a new Page entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new", name="page_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request, Text $text) {
        $page = new Page();
        $page->setUser($this->getUser());
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$page->getExcerpt()) {
                $page->setExcerpt($text->trim($page->getContent(), $this->getParameter('nines_blog.excerpt_length')));
            }
            $page->setSearchable($text->plain($page->getContent()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('success', 'The new page was created.');
            return $this->redirectToRoute('page_show', array('id' => $page->getId()));
        }

        return array(
            'page' => $page,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Page entity.
     *
     * @param Page $page
     *
     * @return array
     *
     * @Route("/{id}", name="page_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(Page $page) {
        if (!$page->getPublic()) {
            $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page.');
        }

        return array(
            'page' => $page,
        );
    }

    /**
     * Displays a form to edit an existing Page entity.
     *
     *
     * @param Request $request
     * @param Page $page
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/edit", name="page_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Page $page, Text $text) {
        $editForm = $this->createForm(PageType::class, $page);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if (!$page->getExcerpt()) {
                $page->setExcerpt($text->trim($page->getContent(), $this->getParameter('nines_blog.excerpt_length')));
            }
            $page->setSearchable($text->plain($page->getContent()));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The page has been updated.');
            return $this->redirectToRoute('page_show', array('id' => $page->getId()));
        }

        return array(
            'page' => $page,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Page entity.
     *
     *
     * @param Request $request
     * @param Page $page
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/delete", name="page_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, Page $page) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();
        $this->addFlash('success', 'The page was deleted.');

        return $this->redirectToRoute('page_index');
    }

}
