<?php

namespace Nines\FeedbackBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\FeedbackBundle\Entity\CommentStatus;
use Nines\FeedbackBundle\Form\CommentStatusType;

/**
 * CommentStatus controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/admin/comment_status")
 */
class CommentStatusController extends Controller
{
    /**
     * Lists all CommentStatus entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="admin_comment_status_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(CommentStatus::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $commentStatuses = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'commentStatuses' => $commentStatuses,
        );
    }

/**
     * Typeahead API endpoint for CommentStatus entities.
     *
     * To make this work, add something like this to CommentStatusRepository:
        //    public function typeaheadQuery($q) {
        //        $qb = $this->createQueryBuilder('e');
        //        $qb->andWhere("e.name LIKE :q");
        //        $qb->orderBy('e.name');
        //        $qb->setParameter('q', "{$q}%");
        //        return $qb->getQuery()->execute();
        //    }
     *
     * @param Request $request
     *
     * @Route("/typeahead", name="admin_comment_status_typeahead")
     * @Method("GET")
     * @return JsonResponse
     */
    public function typeahead(Request $request)
    {
        $q = $request->query->get('q');
        if( ! $q) {
            return new JsonResponse([]);
        }
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository(CommentStatus::class);
        $data = [];
        foreach($repo->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }
        return new JsonResponse($data);
    }
    /**
     * Search for CommentStatus entities.
     *
     * To make this work, add a method like this one to the
     * NinesFeedbackBundle:CommentStatus repository. Replace the fieldName with
     * something appropriate, and adjust the generated search.html.twig
     * template.
     *
     * <code><pre>
     *    public function searchQuery($q) {
     *       $qb = $this->createQueryBuilder('e');
     *       $qb->addSelect("MATCH (e.title) AGAINST(:q BOOLEAN) as HIDDEN score");
     *       $qb->orderBy('score', 'DESC');
     *       $qb->setParameter('q', $q);
     *       return $qb->getQuery();
     *    }
     * </pre></code>
     *
     * @param Request $request
     *
     * @Route("/search", name="admin_comment_status_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository('NinesFeedbackBundle:CommentStatus');
	$q = $request->query->get('q');
	if($q) {
	    $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $commentStatuses = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
	} else {
            $commentStatuses = array();
	}

        return array(
            'commentStatuses' => $commentStatuses,
            'q' => $q,
        );
    }

    /**
     * Creates a new CommentStatus entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new", name="admin_comment_status_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $commentStatus = new CommentStatus();
        $form = $this->createForm(CommentStatusType::class, $commentStatus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($commentStatus);
            $em->flush();

            $this->addFlash('success', 'The new commentStatus was created.');
            return $this->redirectToRoute('admin_comment_status_show', array('id' => $commentStatus->getId()));
        }

        return array(
            'commentStatus' => $commentStatus,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new CommentStatus entity in a popup.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new_popup", name="admin_comment_status_new_popup")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newPopupAction(Request $request)
    {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a CommentStatus entity.
     *
     * @param CommentStatus $commentStatus
     *
     * @return array
     *
     * @Route("/{id}", name="admin_comment_status_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(CommentStatus $commentStatus)
    {

        return array(
            'commentStatus' => $commentStatus,
        );
    }

    /**
     * Displays a form to edit an existing CommentStatus entity.
     *
     *
     * @param Request $request
     * @param CommentStatus $commentStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/edit", name="admin_comment_status_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, CommentStatus $commentStatus)
    {
        $editForm = $this->createForm(CommentStatusType::class, $commentStatus);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The commentStatus has been updated.');
            return $this->redirectToRoute('admin_comment_status_show', array('id' => $commentStatus->getId()));
        }

        return array(
            'commentStatus' => $commentStatus,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a CommentStatus entity.
     *
     *
     * @param Request $request
     * @param CommentStatus $commentStatus
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/delete", name="admin_comment_status_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, CommentStatus $commentStatus)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($commentStatus);
        $em->flush();
        $this->addFlash('success', 'The commentStatus was deleted.');

        return $this->redirectToRoute('admin_comment_status_index');
    }
}
