<?php

namespace Nines\BlogBundle\Controller;

use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Form\PostType;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends Controller {

    /**
     * Lists all Post entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="post_index", methods={"GET"})

     * @Template()
     */
    public function indexAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Post::class);
        $query = $repo->recentQuery($checker->isGranted('ROLE_USER'));
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'posts' => $posts,
        );
    }

    /**
     * Search for Post entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/search", name="post_search", methods={"GET"})

     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Post');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q, $this->isGranted('ROLE_USER'));
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
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new", name="post_new", methods={"GET","POST"})

     * @Template()
     */
    public function newAction(Request $request) {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
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
     * @param Post $post
     *
     * @return array
     *
     * @Route("/{id}", name="post_show", methods={"GET"})

     * @Template()
     */
    public function showAction(Post $post) {

        return array(
            'post' => $post,
        );
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     *
     * @param Request $request
     * @param Post $post
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})

     * @Template()
     */
    public function editAction(Request $request, Post $post) {
        $editForm = $this->createForm(PostType::class, $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
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
     *
     * @param Request $request
     * @param Post $post
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/delete", name="post_delete", methods={"GET"})

     */
    public function deleteAction(Request $request, Post $post) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'The post was deleted.');

        return $this->redirectToRoute('post_index');
    }

}
