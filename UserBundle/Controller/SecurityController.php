<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class SecurityController extends AbstractController {
    use TargetPathTrait;

    protected const IGNORED_ROUTES = [
        'nines_user_security_login',
        'nines_user_security_request_token',
        'nines_user_security_reset_password',
        'nines_user_security_logout',
    ];

    private function setReferrer(Request $request, SessionInterface $session, UrlMatcherInterface $matcher) : void {
        $header = $request->headers->get('referer');
        if ( ! $header) {
            return;
        }

        $parts = parse_url($header);
        if (false === $parts || is_string($parts)) {
            return;
        }
        $referrerHost = $parts['host'] ?? $request->getHost();

        $host = $this->getParameter('router.request_context.host');
        if ( ! $host || $referrerHost !== $host) {
            return;
        }

        if ( ! isset($parts['path'])) {
            return;
        }
        $path = str_replace($request->getBaseUrl(), '', $parts['path']);

        try {
            $route = $matcher->match($path);
        } catch (ResourceNotFoundException $e) {
            // do nothing
            return;
        }

        if (in_array($route['_route'], self::IGNORED_ROUTES, true)) {
            return;
        }
        $this->saveTargetPath($session, 'main', $header);
    }

    /**
     * @Route("/login", name="nines_user_security_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UserManager $manager, SessionInterface $session, RouterInterface $router) : Response {
        if ($this->getUser()) {
            $this->addFlash('success', 'You are already logged in.');

            return $this->redirectToRoute($manager->getAfterLogin());
        }
        $this->setReferrer($request, $session, $router);

        return $this->render('@NinesUser/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * @Route("/request", name="nines_user_security_request_token", methods={"GET", "POST"})
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function request(Request $request, UserManager $manager, EntityManagerInterface $em) : Response {
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

        return $this->render('@NinesUser/security/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset/{token}", name="nines_user_security_reset_password", methods={"GET", "POST"})
     */
    public function reset(Request $request, UserRepository $repository, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em, UserManager $manager, string $token) : Response {
        $user = $repository->findOneBy(['resetToken' => $token]);
        if ( ! $user) {
            $this->addFlash('danger', 'That security token is not valid.');

            return $this->redirectToRoute('homepage');
        }
        if ($user->getResetExpiry() < new DateTimeImmutable()) {
            $this->addFlash('danger', 'The security token has expired. Please try again.');

            return $this->redirectToRoute($manager->getAfterRequest());
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($encoder->encodePassword($user, $form->get('password')->getData()));
            $user->setResetToken(null);
            $user->setResetExpiry(null);
            $em->flush();
            $this->addFlash('success', 'The password has been reset. You should now login to confirm.');

            return $this->redirectToRoute($manager->getAfterReset());
        }

        foreach ($form->getErrors(true, true) as $error) {
            $this->addFlash('danger', $error->getMessage());
        }

        return $this->render('@NinesUser/security/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="nines_user_security_logout")
     */
    public function logout(UserManager $manager) : RedirectResponse {
        return $this->redirectToRoute($manager->getAfterLogout());
    }
}
