<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\PostCategory;
use Nines\BlogBundle\Form\PostCategoryType;
use Nines\BlogBundle\Repository\PostCategoryRepository;
use Nines\BlogBundle\Repository\PostRepository;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post_category")
 */
class PostCategoryController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_blog_post_category_index", methods={"GET"})
     */
    public function index(Request $request, PostCategoryRepository $postCategoryRepository) : Response {
        $query = $postCategoryRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesBlog/post_category/index.html.twig', [
            'post_categories' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/new", name="nines_blog_post_category_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_BLOG_ADMIN")
     */
    public function new(Request $request) : Response {
        $postCategory = new PostCategory();
        $form = $this->createForm(PostCategoryType::class, $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($postCategory);
            $entityManager->flush();
            $this->addFlash('success', 'The new postCategory has been saved.');

            return $this->redirectToRoute('nines_blog_post_category_show', ['id' => $postCategory->getId()]);
        }

        return $this->render('@NinesBlog/post_category/new.html.twig', [
            'post_category' => $postCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="nines_blog_post_category_show", methods={"GET"})
     */
    public function show(Request $request, PostCategory $postCategory, PostRepository $repo) : Response {
        $pageSize = (int) $this->getParameter('page_size');

        $query = $repo->categoryQuery($postCategory, $this->isGranted('ROLE_USER'));
        $posts = $this->paginator->paginate($query, $request->query->getInt('page', 1), $pageSize);

        return $this->render('@NinesBlog/post_category/show.html.twig', [
            'post_category' => $postCategory,
            'posts' => $posts,
        ]);
    }

    /**
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_post_category_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, PostCategory $postCategory) : Response {
        $form = $this->createForm(PostCategoryType::class, $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated postCategory has been saved.');

            return $this->redirectToRoute('nines_blog_post_category_show', ['id' => $postCategory->getId()]);
        }

        return $this->render('@NinesBlog/post_category/edit.html.twig', [
            'post_category' => $postCategory,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}", name="nines_blog_post_category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PostCategory $postCategory) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $postCategory->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($postCategory);
            $entityManager->flush();
            $this->addFlash('success', 'The postCategory has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('nines_blog_post_category_index');
    }
}
