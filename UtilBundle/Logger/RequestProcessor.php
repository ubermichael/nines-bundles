<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Logger;

use Symfony\Component\HttpFoundation\RequestStack;

class RequestProcessor {
    private ?RequestStack $stack = null;

    public function __construct(RequestStack $stack) {
        $this->stack = $stack;
    }

    /**
     * Add the client IP address to the log record.
     *
     * @param array<string,mixed> $record
     *
     * @return array<string,mixed>
     */
    public function __invoke(array $record) : array {
        $request = $this->stack->getCurrentRequest();
        if ( ! $request) {
            return $record;
        }
        $record['extra']['ip'] = $request->getClientIp();
        $record['extra']['url'] = $request->getUri();

        return $record;
    }
}
