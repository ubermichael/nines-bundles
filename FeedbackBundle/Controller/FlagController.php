<?php

namespace Nines\FeedbackBundle\Controller;

use Exception;
use Nines\FeedbackBundle\Entity\Flag;
use Nines\FeedbackBundle\Form\FlagEntityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Flag controller.
 *
 * @Route("/flag")
 */
class FlagController extends Controller
{
    /**
     * Lists all Flag entities.
     *
     * @Route("/", name="flag_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $dql = 'SELECT e FROM NinesFeedbackBundle:Flag e ORDER BY e.id';
        $query = $em->createQuery($dql);
        $paginator = $this->get('knp_paginator');
        $flags = $paginator->paginate($query, $request->query->getint('page', 1), 25);

        return array(
            'flags' => $flags,
        );
    }
    
    /**
     * Post flags to an entity.
     * 
     * @param Request $request
     * @Route("/post", name="flag_post")
     * @Template()
     */
    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $service = $this->get('feedback.flag');
        $id = $request->request->get('entity_id', null);
        $class = $request->request->get('entity_class', null);
        if( ! $service->acceptsFlags($class)) {
            throw new Exception("Cannot add flags for this class.");            
        }
        $repo = $em->getRepository($class);
        $entity = $repo->find($id);
        $form = $this->createForm(FlagEntityType::class, null, array(
            'entity_manager' => $em,
            'flags' => array(),
        ));
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $service->setFlags($entity, $form->getData()['flags']);
            $this->addFlash('success', 'The flags have been updated.');
            return $this->redirect($service->entityUrl($entity));
        }
        return array(
            'entity' => $entity,
            'service' => $service,
        );
    }

    /**
     * Creates a new Flag entity.
     *
     * @Route("/new", name="flag_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        $flag = new Flag();
        $form = $this->createForm('Nines\FeedbackBundle\Form\FlagType', $flag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($flag);
            $em->flush();

            $this->addFlash('success', 'The new flag was created.');
            return $this->redirectToRoute('flag_show', array('id' => $flag->getId()));
        }

        return array(
            'flag' => $flag,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Flag entity.
     *
     * @Route("/{id}", name="flag_show")
     * @Method("GET")
     * @Template()
	 * @param Flag $flag
     */
    public function showAction(Flag $flag)
    {

        return array(
            'flag' => $flag,
        );
    }

    /**
     * Displays a form to edit an existing Flag entity.
     *
     * @Route("/{id}/edit", name="flag_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param Flag $flag
     */
    public function editAction(Request $request, Flag $flag)
    {
        $editForm = $this->createForm('Nines\FeedbackBundle\Form\FlagType', $flag);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The flag has been updated.');
            return $this->redirectToRoute('flag_show', array('id' => $flag->getId()));
        }

        return array(
            'flag' => $flag,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Flag entity.
     *
     * @Route("/{id}/delete", name="flag_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param Flag $flag
     */
    public function deleteAction(Request $request, Flag $flag)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($flag);
        $em->flush();
        $this->addFlash('success', 'The flag was deleted.');

        return $this->redirectToRoute('flag_index');
    }
}
