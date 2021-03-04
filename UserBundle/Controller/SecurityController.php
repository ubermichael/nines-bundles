<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Controller;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Form\Security\RequestTokenType;
use Nines\UserBundle\Form\Security\ResetPasswordType;
use Nines\UserBundle\Repository\UserRepository;
use Nines\UserBundle\Services\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {
    /**
     * @Route("/login", name="nines_user_security_login")
     * @Template
     *
     * @return array|RedirectResponse
     */
    public function login(AuthenticationUtils $authenticationUtils, UserManager $manager) {
        if ($this->getUser()) {
            $this->addFlash('success', 'You are already logged in.');

            return $this->redirectToRoute($manager->getAfterLogin());
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'last_username' => $lastUsername,
            'error' => $error,
        ];
    }

    /**
     * @Route("/request", name="nines_user_security_request_token", methods={"GET", "POST"})
     * @Template
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @throws Exception
     *
     * @return array|RedirectResponse
     */
    public function request(Request $request, UserManager $manager, EntityManagerInterface $em) {
        $form = $this->createForm(RequestTokenType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $manager->find($form->get('email')->getData());
            if ($user) {
                $manager->requestReset($user);
                $em->flush();
                $manager->sendReset($user, ['ip' => $request->getClientIp()]);
            }
            $this->addFlash('success', 'The password reset email has been sent.');

            return $this->redirectToRoute($manager->getAfterRequest());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/reset/{token}", name="nines_user_security_reset_password", methods={"GET", "POST"})
     * @Template
     * @IsGranted("IS_AUTHENTICATED_ANONYMOUSLY")
     *
     * @param string $token
     *
     * @throws Exception
     *
     * @return array|RedirectResponse
     */
    public function reset(Request $request, UserRepository $repository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, $token) {
        $user = $repository->findOneBy(['resetToken' => $token]);
        if ( ! $user) {
            $this->addFlash('failure', 'That security token is not valid.');

            return $this->redirectToRoute('homepage');
        }
        if ($user->getResetExpiry() < new DateTimeImmutable()) {
            $this->addFlash('failure', 'The security token has expired. Please try again.');

            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $form->get('password')->getData()));
            $em->flush();
            $this->addFlash('success', 'The password has been reset. You should now login to confirm.');

            return $this->redirectToRoute('nines_user_security_login');
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/logout", name="nines_user_security_logout")
     */
    public function logout() : RedirectResponse {
        return $this->redirectToRoute('homepage');
    }
}
