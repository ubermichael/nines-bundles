<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Security;

use Nines\UserBundle\Entity\User;
use Nines\UserBundle\Exception\AccountInactiveException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
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
     * @throws AccountStatusException
     * @throws AccountInactiveException
     */
    public function checkPostAuth(UserInterface $user) : void {
        if ( ! $user instanceof User) {
            return;
        }

        if ( ! $user->isActive()) {
            $exception = new AccountInactiveException('The user account is not active.');
            $exception->setUser($user);

            throw $exception;
        }
    }
}
