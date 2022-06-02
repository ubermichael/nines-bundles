<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\PostStatus;
use Nines\BlogBundle\Form\PostStatusType;
use Nines\BlogBundle\Repository\PostRepository;
use Nines\BlogBundle\Repository\PostStatusRepository;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/post_status")
 */
class PostStatusController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="nines_blog_post_status_index", methods={"GET"})
     */
    public function index(Request $request, PostStatusRepository $postStatusRepository) : Response {
        $query = $postStatusRepository->indexQuery();
        $pageSize = (int) $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return $this->render('@NinesBlog/post_status/index.html.twig', [
            'post_statuses' => $this->paginator->paginate($query, $page, $pageSize),
        ]);
    }

    /**
     * @Route("/new", name="nines_blog_post_status_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_BLOG_ADMIN")
     */
    public function new(Request $request) : Response {
        $postStatus = new PostStatus();
        $form = $this->createForm(PostStatusType::class, $postStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($postStatus);
            $entityManager->flush();
            $this->addFlash('success', 'The new postStatus has been saved.');

            return $this->redirectToRoute('nines_blog_post_status_show', ['id' => $postStatus->getId()]);
        }

        return $this->render('@NinesBlog/post_status/new.html.twig', [
            'post_status' => $postStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="nines_blog_post_status_show", methods={"GET"})
     */
    public function show(Request $request, PostStatus $postStatus, PostRepository $repo) : Response {
        $pageSize = (int) $this->getParameter('page_size');
        $query = $repo->statusQuery($postStatus, $this->isGranted('ROLE_USER'));
        $posts = $this->paginator->paginate($query, $request->query->getInt('page', 1), $pageSize);

        return $this->render('@NinesBlog/post_status/show.html.twig', [
            'post_status' => $postStatus,
            'posts' => $posts,
        ]);
    }

    /**
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_post_status_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, PostStatus $postStatus) : Response {
        $form = $this->createForm(PostStatusType::class, $postStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated postStatus has been saved.');

            return $this->redirectToRoute('nines_blog_post_status_show', ['id' => $postStatus->getId()]);
        }

        return $this->render('@NinesBlog/post_status/edit.html.twig', [
            'post_status' => $postStatus,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}", name="nines_blog_post_status_delete", methods={"DELETE"})
     */
    public function delete(Request $request, PostStatus $postStatus) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete' . $postStatus->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($postStatus);
            $entityManager->flush();
            $this->addFlash('success', 'The postStatus has been deleted.');
        } else {
            $this->addFlash('warning', 'The security token was not valid.');
        }

        return $this->redirectToRoute('nines_blog_post_status_index');
    }
}
