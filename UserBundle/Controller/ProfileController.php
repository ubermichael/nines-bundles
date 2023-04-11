<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Controller;

use Nines\UserBundle\Form\Profile\ChangePasswordType;
use Nines\UserBundle\Form\Profile\ProfileType;
use Nines\UserBundle\Services\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class ProfileController extends AbstractController {
    /**
     * @Route("/", name="nines_user_profile_index", methods={"GET"})
     * @Template
     */
    public function index() : Response {
        $user = $this->getUser();

        return $this->render('@NinesUser/profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/edit", name="nines_user_profile_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, UserPasswordEncoderInterface $encoder) : Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($encoder->isPasswordValid($user, $form->get('password')->getData())) {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Your profile has been updated.');

                return $this->redirectToRoute('nines_user_profile_index');
            }
            $this->addFlash('failure', 'The password does not match.');
        }

        return $this->render('@NinesUser/profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/password", name="nines_user_profile_password", methods={"GET", "POST"})
     */
    public function password(Request $request, UserManager $manager) : Response {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $current = $form->get('current_password')->getData();
            if ($manager->validatePassword($user, $current)) {
                $password = $form->get('new_password')->getData();
                $manager->changePassword($user, $password);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Your password has been updated.');

                return $this->redirectToRoute('nines_user_profile_index');
            }
            $this->addFlash('failure', 'The password does not match.');
        }

        return $this->render('@NinesUser/profile/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
