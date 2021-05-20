<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Entity\PostCategory;
use Nines\BlogBundle\Form\PostCategoryType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * PostCategory controller.
 *
 * @Route("/post_category")
 */
class PostCategoryController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all PostCategory entities.
     *
     * @return array
     *
     * @Route("/", name="nines_blog_post_category_index", methods={"GET"})
     *
     * @Template
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(PostCategory::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();

        $postCategories = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'postCategories' => $postCategories,
        ];
    }

    /**
     * Typeahead API endpoint for PostCategory entities.
     *
     * @Route("/typeahead", name="nines_blog_post_category_typeahead", methods={"GET"})
     * @IsGranted("ROLE_BLOG_ADMIN")
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(PostCategory::class);
        $data = [];

        foreach ($repo->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Creates a new PostCategory entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new", name="nines_blog_post_category_new", methods={"GET", "POST"})
     *
     * @Template
     */
    public function newAction(Request $request) {
        $postCategory = new PostCategory();
        $form = $this->createForm(PostCategoryType::class, $postCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($postCategory);
            $em->flush();

            $this->addFlash('success', 'The new postCategory was created.');

            return $this->redirectToRoute('nines_blog_post_category_show', ['id' => $postCategory->getId()]);
        }

        return [
            'postCategory' => $postCategory,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a new PostCategory entity in a popup.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new_popup", name="nines_blog_post_category_new_popup", methods={"GET"})
     *
     * @Template
     */
    public function newPopupAction(Request $request) {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a PostCategory entity.
     *
     * @return array
     *
     * @Route("/{id}", name="nines_blog_post_category_show", methods={"GET"})
     *
     * @Template
     */
    public function showAction(Request $request, PostCategory $postCategory) {
        $repo = $this->getDoctrine()->getManager()->getRepository(PostCategory::class);
        $query = $repo->getPosts($postCategory, $this->isGranted('ROLE_USER'))->execute();

        $posts = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'postCategory' => $postCategory,
            'posts' => $posts,
        ];
    }

    /**
     * Displays a form to edit an existing PostCategory entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_post_category_edit", methods={"GET"})
     *
     * @Template
     */
    public function editAction(Request $request, PostCategory $postCategory) {
        $editForm = $this->createForm(PostCategoryType::class, $postCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postCategory has been updated.');

            return $this->redirectToRoute('nines_blog_post_category_show', ['id' => $postCategory->getId()]);
        }

        return [
            'postCategory' => $postCategory,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a PostCategory entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/delete", name="nines_blog_post_category_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, PostCategory $postCategory) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postCategory);
        $em->flush();
        $this->addFlash('success', 'The postCategory was deleted.');

        return $this->redirectToRoute('nines_blog_post_category_index');
    }
}
