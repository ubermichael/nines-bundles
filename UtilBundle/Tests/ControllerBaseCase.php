<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Tests;

use Nines\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

abstract class ControllerBaseCase extends BaseCase {
    /**
     * @var null|KernelBrowser
     */
    protected $client;

    /**
     * @param $reference string The named reference loaded from a fixture.
     *
     * @return User
     */
    protected function login($reference) : User {
        /** @var User $user */
        $user = $this->references->getReference($reference);
        $token = new PostAuthenticationGuardToken($user, 'main', $user->getRoles());

        $session = $this->client->getContainer()->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $user;
    }

    protected function setUp() : void {
        parent::setUp();
        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }
}
