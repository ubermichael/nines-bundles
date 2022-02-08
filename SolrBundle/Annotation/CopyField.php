<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

use Doctrine\ORM\Mapping\Annotation;

/**
 * Copy data from one or more fields into a destination field.
 * Eg. @Solr\CopyField(from={"one", "two", "three"}, to="destination", type="texts").
 *
 * @Annotation
 * @Target("ANNOTATION")
 */
class CopyField {
    /**
     * Source fields.
     *
     * @var array<string>
     */
    public array $from = [];

    /**
     * Destination fields.
     */
    public ?string $to = null;

    /**
     * The tpe of the destination field.
     */
    public ?string $type = null;
}
