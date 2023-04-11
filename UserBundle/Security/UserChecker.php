<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Security;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Exception\AccountInactiveException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface {
    /**
     * Checks the user account before authentication.
     *
     * @throws AccountStatusException
     */
    public function checkPreAuth(UserInterface $user) : void {
        // Don't need to do anything here.
    }

    /**
     * Checks the user account after authentication.
     *
     * @throws AccountInactiveException
     * @throws AccountStatusException
     */
    public function checkPostAuth(UserInterface $user) : void {
        if ( ! $user instanceof User) {
            throw new AuthenticationException('Unknown user type');
        }

        if ( ! $user->isActive()) {
            throw new CustomUserMessageAuthenticationException('The user account is not active.');
        }
    }
}
