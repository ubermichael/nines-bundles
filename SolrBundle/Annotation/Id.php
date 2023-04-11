<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

/**
 * This annotation should be applied to the identifer property of an entity.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Id {
    /**
     * Name of the ID property.
     */
    public ?string $name = null;

    /**
     * A method from the indexed entity to get the ID of an entity.
     */
    public ?string $getter = null;
}
