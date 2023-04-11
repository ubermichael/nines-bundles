<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Security;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Services\UserManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface {
    use TargetPathTrait;

    private ?UrlGeneratorInterface $urlGenerator = null;

    private ?CsrfTokenManagerInterface $csrfTokenManager = null;

    private ?UserPasswordEncoderInterface $passwordEncoder = null;

    private ?UserManager $userManager = null;

    protected function getLoginUrl() : string {
        return $this->urlGenerator->generate('nines_user_security_login');
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setUserManager(UserManager $userManager) : void {
        $this->userManager = $userManager;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator) : void {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setCsrfTokenManager(CsrfTokenManagerInterface $csrfTokenManager) : void {
        $this->csrfTokenManager = $csrfTokenManager;
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setPasswordEncoder(UserPasswordEncoderInterface $passwordEncoder) : void {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request) : bool {
        return 'nines_user_security_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    /**
     * @return array<string,mixed>
     */
    public function getCredentials(Request $request) : array {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email'],
        );

        return $credentials;
    }

    /**
     * @phpstan-param array<string,mixed> $credentials
     *
     * @param mixed $credentials
     */
    public function getUser($credentials, UserProviderInterface $userProvider) : User {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if ( ! $this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->userManager->find($credentials['email']);

        if ( ! $user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user) : bool {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * @param mixed $credentials
     */
    public function getPassword($credentials) : ?string {
        return $credentials['password'] ?? null;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) : Response {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate($this->userManager->getAfterLogin()));
    }
}
