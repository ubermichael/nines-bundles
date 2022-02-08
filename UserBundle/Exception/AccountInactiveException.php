<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UserBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountInactiveException extends AccountStatusException {
    public function getMessageKey() : string {
        return 'The user account is not active.';
    }
}
