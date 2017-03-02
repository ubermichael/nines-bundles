<?php

namespace Nines\UserBundle\Controller;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Form\AdminUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin-only user controller.
 *
 * @Route("/admin/user")
 */
class AdminUserController extends Controller
{
    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     * 
     * @return array
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NinesUserBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new User entity.
     *
     * @Route("/", name="user_create")
     * @Method("POST")
     * @Template("NinesUserBundle:User:new.html.twig")
     * 
     * @param Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setPlainPassword(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 15));
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->addFlash('Success', 'The user has been created with a random password. The user should initiate password recovery.');

            return $this->redirect($this->generateUrl('user_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(AdminUserType::class, $entity, array(
            'action' => $this->generateUrl('user_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method("GET")
     * @Template()
     * 
     * @return array
     */
    public function newAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a User entity.
     *
     * @Route("/{id}", name="user_show")
     * @Method("GET")
     * @Template()
     * 
     * @param string $id
     *
     * @return array
     */
    public function showAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NinesUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method("GET")
     * @Template()
     * 
     * @param string $id
     *
     * @return array
     */
    public function editAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('NinesUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a User entity.
     *
     * @param User $entity The entity
     *
     * @return Form The form
     */
    private function createEditForm(User $entity)
    {
        $form = $this->createForm(AdminUserType::class, $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     * @Route("/{id}", name="user_update")
     * @Method("PUT")
     * @Template("NinesUserBundle:User:edit.html.twig")
     * 
     * @param Request $request
     * @param string  $id
     *
     * @return array
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NinesUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $userManager->updateUser($entity);
            $this->addFlash('success', 'User info updated.');

            return $this->redirect($this->generateUrl('user_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a User entity.
     *
     * @Route("/{id}/delete", name="user_delete")
     * 
     * @param string $id
     *
     * @return array
     */
    public function deleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NinesUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Change a user's password.
     * 
     * @Route("/{id}/password", name="admin_user_password")
     * @Method({"GET", "POST"})
     * @Template()
     * 
     * @param Request $request
     * @param int     $id
     *
     * @return array
     */
    public function passwordAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NinesUserBundle:User')->find($id);

        $builder = $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_password', array('id' => $id)))
            ->setMethod('POST')
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match',
                'required' => true,
                'first_options' => array('label' => 'Password'),
                'second_options' => array('label' => 'Password Confirm'),
            ));
        $builder->add('submit', 'submit', array('label' => 'Change'));
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $data = $form->getData();
            $entity->setPlainPassword($data['password']);
            $userManager->updateUser($entity);
            $this->addFlash('success', 'Password successfully changed.');

            return $this->redirect($this->generateUrl('user_show', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }
}
