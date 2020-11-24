<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\BlogBundle\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\BlogBundle\Entity\Page;
use Nines\BlogBundle\Form\PageType;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Nines\UtilBundle\Services\Text;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * Lists all Page entities.
     *
     * @return array
     *
     * @Route("/", name="nines_blog_page_index", methods={"GET"})
     *
     * @Template
     */
    public function indexAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Page::class);
        $query = $repo->listQuery($checker->isGranted('ROLE_USER'));
        $pages = $this->paginator->paginate($query, $request->query->getint('page', 1), 25);

        return [
            'pages' => $pages,
        ];
    }

    /**
     * @Route("/sort", name="nines_blog_page_sort", methods={"GET", "POST"})
     *
     * @Template
     * @IsGranted("ROLE_BLOG_ADMIN")
     *
     * @return array
     */
    public function sortAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Page');

        if ('POST' === $request->getMethod()) {
            $order = $request->request->get('order');
            $list = explode(',', $order);

            for ($i = 0; $i < count($list); $i++) {
                $page = $repo->find($list[$i]);
                $page->setWeight($i + 1);
            }
            $em->flush();
            $this->addFlash('success', 'The pages have been ordered.');

            return $this->redirect($this->generateUrl('page_sort'));
        }

        $pages = $repo->findBy(
            ['public' => true],
            ['weight' => 'ASC', 'title' => 'ASC']
        );

        return [
            'pages' => $pages,
        ];
    }

    /**
     * Full text search for Page entities.
     *
     * @Route("/search", name="nines_blog_page_search", methods={"GET"})
     *
     * @Template
     *
     * @return array
     */
    public function searchAction(Request $request, AuthorizationCheckerInterface $checker) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('NinesBlogBundle:Page');
        $q = $request->query->get('q');
        if ($q) {
            $query = $repo->fulltextQuery($q, $checker->isGranted('ROLE_USER'));

            $pages = $this->paginator->paginate($query, $request->query->getInt('page', 1), 25);
        } else {
            $pages = [];
        }

        return [
            'pages' => $pages,
            'q' => $q,
        ];
    }

    /**
     * Creates a new Page entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/new", name="nines_blog_page_new", methods={"GET", "POST"})
     *
     * @Template
     */
    public function newAction(Request $request, Text $text) {
        $page = new Page();
        $page->setUser($this->getUser());
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->setSearchable($text->plain($page->getContent()));
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->addFlash('success', 'The new page was created.');

            return $this->redirectToRoute('nines_blog_page_show', ['id' => $page->getId()]);
        }

        return [
            'page' => $page,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a Page entity.
     *
     * @return array
     *
     * @Route("/{id}", name="nines_blog_page_show", methods={"GET"})
     *
     * @Template
     */
    public function showAction(Page $page) {
        if ( ! $page->getPublic()) {
            $this->denyAccessUnlessGranted('ROLE_USER', null, 'Unable to access this page.');
        }

        return [
            'page' => $page,
        ];
    }

    /**
     * Displays a form to edit an existing Page entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/edit", name="nines_blog_page_edit", methods={"GET", "POST"})
     *
     * @Template
     */
    public function editAction(Request $request, Page $page, Text $text) {
        $editForm = $this->createForm(PageType::class, $page);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $page->setSearchable($text->plain($page->getContent()));
            $em = $this->getDoctrine()->getManager();
            if ($page->getHomepage()) {
                // make sure all other pages are NOT the home page.
                $repo = $em->getRepository(Page::class);
                $repo->clearHomepages($page);
            }

            $em->flush();
            $this->addFlash('success', 'The page has been updated.');

            return $this->redirectToRoute('nines_blog_page_show', ['id' => $page->getId()]);
        }

        return [
            'page' => $page,
            'edit_form' => $editForm->createView(),
        ];
    }

    /**
     * Deletes a Page entity.
     *
     * @return array|RedirectResponse
     *
     * @IsGranted("ROLE_BLOG_ADMIN")
     * @Route("/{id}/delete", name="nines_blog_page_delete", methods={"GET"})
     */
    public function deleteAction(Request $request, Page $page) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();
        $this->addFlash('success', 'The page was deleted.');

        return $this->redirectToRoute('nines_blog_page_index');
    }
}
