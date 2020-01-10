<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Form\PostType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Post controller.
 *
 * @Route("/post")
 */
class PostController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all Post entities.
     *
     * @return array
     *
     * @Route("/", name="nines_blog_post_index", methods={"GET"})
     *
     * @Template()
     */
    public function indexAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Post::class);
        $query = $repo->recentQuery($checker->isGranted('ROLE_USER'));
        $posts = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'posts' => $posts,
        ];
    }

    /**
     * Search for Post entities.
     *
     * @return array
     *
     * @Route("/search", name="nines_blog_post_search", methods={"GET"})
     *
     * @Template()
     */
    public function searchAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Post');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q, $this->isGranted('ROLE_USER'));
            $posts = $this->paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $posts = [];
        }

        return [
            'posts' => $posts,
            'q' => $q,
        ];
    }

    /**
     * Creates a new Post entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new", name="nines_blog_post_new", methods={"GET","POST"})
     *
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

            return $this->redirectToRoute('nines_blog_post_show', ['id' => $post->getId()]);
        }

        return [
            'post' => $post,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Post entity.
     *
     * @return array
     *
     * @Route("/{id}", name="nines_blog_post_show", methods={"GET"})
     *
     * @Template()
     */
    public function showAction(Post $post) {
        return [
            'post' => $post,
        ];
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_post_edit", methods={"GET","POST"})
     *
     * @Template()
     */
    public function editAction(Request $request, Post $post) {
        $editForm = $this->createForm(PostType::class, $post);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The post has been updated.');

            return $this->redirectToRoute('nines_blog_post_show', ['id' => $post->getId()]);
        }

        return [
            'post' => $post,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a Post entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/delete", name="nines_blog_post_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, Post $post) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        $this->addFlash('success', 'The post was deleted.');

        return $this->redirectToRoute('nines_blog_post_index');
    }
}
