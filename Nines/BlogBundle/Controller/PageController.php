<?php

namespace Nines\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Form\PageType;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     * Lists all Page entities.
     *
     * @Route("/", name="page_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $private = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $repo = $em->getRepository(Page::class);
        $query = $repo->listQuery($private);
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
     * 
     * @param Request $request
     * @return array
     */
    public function sortAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Page');
        
        if($request->getMethod() === 'POST') {
            $order = $request->request->get('order');
            $list = explode(',', $order);
            for($i = 0; $i < count($list); ++$i) {
                $page = $repo->find($list[$i]);
                $page->setWeight($i+1);
                $em->flush($page);
            }
            $this->addFlash('success', 'The pages have been ordered.');
            return $this->redirect($this->generateUrl('page_sort'));
        }
        
        $pages = $repo->findBy(
            array('public' => true), 
            array('weight' => 'ASC', 'title' => 'ASC')
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
	 * @param Request $request
	 * @return array
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('NinesBlogBundle:Page');
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q);
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
     * @Route("/new", name="page_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        if( ! $this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $user = $this->getUser();
        $page = new Page();
        $page->setUser($user);
        
        $form = $this->createForm('Nines\BlogBundle\Form\PageType', $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $text = $this->get('nines.util.text');
            if( ! $page->getExcerpt()) {
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
     * @Route("/{id}", name="page_show")
     * @Method("GET")
     * @Template()
	 * @param Page $page
     */
    public function showAction(Page $page)
    {
        if( ! $page->getPublic()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        }

        return array(
            'page' => $page,
        );
    }

    /**
     * Displays a form to edit an existing Page entity.
     *
     * @Route("/{id}/edit", name="page_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param Page $page
     */
    public function editAction(Request $request, Page $page)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        $editForm = $this->createForm('Nines\BlogBundle\Form\PageType', $page);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $text = $this->get('nines.util.text');
            if( ! $page->getExcerpt()) {
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
     * @Route("/{id}/delete", name="page_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param Page $page
     */
    public function deleteAction(Request $request, Page $page)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();
        $this->addFlash('success', 'The page was deleted.');

        return $this->redirectToRoute('page_index');
    }
}
