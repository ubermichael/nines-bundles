<?php

namespace Nines\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Form\UserType;

/**
 * User controller.
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/admin/user")
 */
class UserController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/", name="admin_user_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(User::class, 'e')->orderBy('e.id', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'users' => $users,
        );
    }

/**
     * Typeahead API endpoint for User entities.
     *
     * To make this work, add something like this to UserRepository:
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
     * @Route("/typeahead", name="admin_user_typeahead")
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
	$repo = $em->getRepository(User::class);
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
     * Search for User entities.
     *
     * To make this work, add a method like this one to the
     * NinesUserBundle:User repository. Replace the fieldName with
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
     * @Route("/search", name="admin_user_search")
     * @Method("GET")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
	$repo = $em->getRepository('NinesUserBundle:User');
	$q = $request->query->get('q');
	if($q) {
	    $query = $repo->searchQuery($q);
            $paginator = $this->get('knp_paginator');
            $users = $paginator->paginate($query, $request->query->getInt('page', 1), 25);
	} else {
            $users = array();
	}

        return array(
            'users' => $users,
            'q' => $q,
        );
    }

    /**
     * Creates a new User entity.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'The new user was created.');
            return $this->redirectToRoute('admin_user_show', array('id' => $user->getId()));
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a new User entity in a popup.
     *
     * @param Request $request
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/new_popup", name="admin_user_new_popup")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function newPopupAction(Request $request)
    {
        return $this->newAction($request);
    }

    /**
     * Finds and displays a User entity.
     *
     * @param User $user
     *
     * @return array
     *
     * @Route("/{id}", name="admin_user_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction(User $user)
    {

        return array(
            'user' => $user,
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     *
     * @param Request $request
     * @param User $user
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/edit", name="admin_user_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, User $user)
    {
        $editForm = $this->createForm(UserType::class, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The user has been updated.');
            return $this->redirectToRoute('admin_user_show', array('id' => $user->getId()));
        }

        return array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a User entity.
     *
     *
     * @param Request $request
     * @param User $user
     *
     * @return array|RedirectResponse
     *
     * @Security("has_role('ROLE_CONTENT_ADMIN')")
     * @Route("/{id}/delete", name="admin_user_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'The user was deleted.');

        return $this->redirectToRoute('admin_user_index');
    }
}
