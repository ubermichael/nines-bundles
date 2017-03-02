<?php

namespace Nines\BlogBundle\Controller;

use Nines\BlogBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller
{
    /**
     * Lists all Post entities.
     *
     * @Route("/", name="post_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {        
        $em = $this->getDoctrine()->getManager();
        $private = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
        $repo = $em->getRepository(Post::class);
        $query = $repo->recentQuery($private);
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'posts' => $posts,
        );
    }
    
    /**
     * Full text search for Post entities.
     *
     * @Route("/fulltext", name="post_search")
     * @Method("GET")
     * @Template()
	 * @param Request $request
	 * @return array
     */
    public function fulltextAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $private = $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN');
		$repo = $em->getRepository(Post::class);
		$q = $request->query->get('q');
		if($q) {
	        $query = $repo->fulltextQuery($q, $private);
			$paginator = $this->get('knp_paginator');
			$posts = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
		} else {
			$posts = array();
		}

        return array(
            'posts' => $posts,
			'q' => $q,
        );
    }

    /**
     * Creates a new Post entity.
     *
     * @Route("/new", name="post_new")
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        
        $post = new Post();
        $post->setUser($user);
        $post->setCategory($em->getRepository('NinesBlogBundle:PostCategory')->findOneBy(array(
            'name' => $this->getParameter('nines_blog.default_category'),
        )));
        $post->setStatus($em->getRepository('NinesBlogBundle:PostStatus')->findOneBy(array(
            'name' => $this->getParameter('nines_blog.default_status'),
        )));        
        $form = $this->createForm('Nines\BlogBundle\Form\PostType', $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $text = $this->get('nines.util.text');
            if( ! $post->getExcerpt()) {
                $post->setExcerpt($text->trim($post->getContent(), $this->getParameter('nines_blog.excerpt_length')));
            }
            $post->setSearchable($text->plain($post->getContent()));
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'The new post was created.');
            return $this->redirectToRoute('post_show', array('id' => $post->getId()));
        }

        return array(
            'post' => $post,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Post entity.
     *
     * @Route("/{id}", name="post_show")
     * @Method("GET")
     * @Template()
	 * @param Post $post
     */
    public function showAction(Request $request, Post $post)
    {
        if( ! $post->getStatus()->getPublic()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        }
        return array(
            'post' => $post,
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id}/edit", name="post_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param Post $post
     */
    public function editAction(Request $request, Post $post)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        $editForm = $this->createForm('Nines\BlogBundle\Form\PostType', $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $text = $this->get('nines.util.text');
            if( ! $post->getExcerpt()) {
                $post->setExcerpt($text->trim($post->getContent(), $this->getParameter('nines_blog.excerpt_length')));
            }
            $post->setSearchable($text->plain($post->getContent()));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The post has been updated.');
            return $this->redirectToRoute('post_show', array('id' => $post->getId()));
        }

        return array(
            'post' => $post,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Post entity.
     *
     * @Route("/{id}/delete", name="post_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param Post $post
     */
    public function deleteAction(Request $request, Post $post)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');            
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'The post was deleted.');

        return $this->redirectToRoute('post_index');
    }
}
