<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Exception;

use Throwable;

class MappingException extends SolrException {
    public const CODE = 2;

    public const MESSAGE = 'The index mapping is misconfigured.';

    public function __construct(?string $message = self::MESSAGE, ?int $code = self::CODE, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
