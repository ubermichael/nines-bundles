<?php

namespace Nines\UserBundle\Controller;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Form\AdminUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Admin-only user controller.
 *
 * @Route("/admin/user")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminUserController extends Controller {

    /**
     * Lists all User entities.
     *
     * @Route("/", name="user")
     * @Method("GET")
     * @Template()
     *
     * @return array
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NinesUserBundle:User')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="user_new")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function newAction(Request $request) {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user, array(
            'permission_levels' => $this->getParameter('nines_user.permission_levels')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPlainPassword(substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 15));
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('Success', 'The user has been created with a random password. The user should initiate password recovery.');

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return array(
            'entity' => $user,
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
    public function showAction(User $user) {
        return array(
            'entity' => $user,
        );
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     * @Template()
     *
     * @param string $id
     *
     * @return array
     */
    public function editAction(Request $request, User $user) {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The user has been updated.');

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return array(
            'entity' => $user,
            'edit_form' => $form->createView(),
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
    public function deleteAction(User $user) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', "The user has been removed.");
        return $this->redirectToRoute('user');
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
    public function passwordAction(Request $request, User $user) {
        $builder = $this->createFormBuilder();
        $builder->setMethod('POST');
        $builder->add('password', RepeatedType::class, array(
            'type' => PasswordType::class,
            'invalid_message' => 'The password fields must match',
            'required' => true,
            'first_options' => array('label' => 'Password'),
            'second_options' => array('label' => 'Password Confirm'),
        ));
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');
            $data = $form->getData();
            $user->setPlainPassword($data['password']);
            $userManager->updateUser($user);
            $this->addFlash('success', 'Password successfully changed.');

            return $this->redirectToRoute('user_show', array('id' => $user->getId()));
        }

        return array(
            'entity' => $user,
            'form' => $form->createView(),
        );
    }

}
