<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Controller;

use Knp\Component\Pager\PaginatorInterface;

/**
 * Convienence trait for the use of paginators.
 */
trait PaginatorTrait {
    protected ?PaginatorInterface $paginator = null;

    /**
     * Set the paginator service.
     */
    public function setPaginator(PaginatorInterface $paginator) : self {
        $this->paginator = $paginator;

        return $this;
    }
}
