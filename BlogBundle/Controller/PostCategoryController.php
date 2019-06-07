<?php

namespace Nines\BlogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nines\BlogBundle\Entity\PostCategory;
use Nines\BlogBundle\Entity\Post;
use Nines\BlogBundle\Form\PostCategoryType;

/**
 * PostCategory controller.
 *
 * @Route("/post_category")
 */
class PostCategoryController extends Controller {

    /**
     * Lists all PostCategory entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="post_category_index", methods={"GET"})

     * @Template()
     */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(PostCategory::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $postCategories = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'postCategories' => $postCategories,
        );
    }

    /**
     * Typeahead API endpoint for PostCategory entities.
     *
     * @param Request $request
     *
     * @Route("/typeahead", name="post_category_typeahead", methods={"GET"})
     * @Security("has_role('ROLE_BLOG_ADMIN')")

     * @return JsonResponse
     */
    public function typeahead(Request $request) {
        $q = $request->query->get('q');
        if (!$q) {
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
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new", name="post_category_new", methods={"GET"})

     * @Template()
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
            return $this->redirectToRoute('post_category_show', array('id' => $postCategory->getId()));
        }

        return array(
            'postCategory' => $postCategory,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new PostCategory entity in a popup.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/new_popup", name="post_category_new_popup", methods={"GET"})

     * @Template()
     */
    public function newPopupAction(Request $request) {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a PostCategory entity.
     *
     * @param PostCategory $postCategory
     *
     * @return array
     *
     * @Route("/{id}", name="post_category_show", methods={"GET"})

     * @Template()
     */
    public function showAction(Request $request, PostCategory $postCategory) {
        $repo = $this->getDoctrine()->getManager()->getRepository(PostCategory::class);
        $query = $repo->getPosts($postCategory, $this->isGranted('ROLE_USER'))->execute();
        $paginator = $this->get('knp_paginator');
        $posts = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'postCategory' => $postCategory,
            'posts' => $posts,
        );
    }

    /**
     * Displays a form to edit an existing PostCategory entity.
     *
     *
     * @param Request $request
     * @param PostCategory $postCategory
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/edit", name="post_category_edit", methods={"GET"})

     * @Template()
     */
    public function editAction(Request $request, PostCategory $postCategory) {
        $editForm = $this->createForm(PostCategoryType::class, $postCategory);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The postCategory has been updated.');
            return $this->redirectToRoute('post_category_show', array('id' => $postCategory->getId()));
        }

        return array(
            'postCategory' => $postCategory,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a PostCategory entity.
     *
     *
     * @param Request $request
     * @param PostCategory $postCategory
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_BLOG_ADMIN')")
     * @Route("/{id}/delete", name="post_category_delete", methods={"GET"})

     */
    public function deleteAction(Request $request, PostCategory $postCategory) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($postCategory);
        $em->flush();
        $this->addFlash('success', 'The postCategory was deleted.');

        return $this->redirectToRoute('post_category_index');
    }

}
