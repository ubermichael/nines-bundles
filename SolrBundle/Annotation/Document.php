<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

/**
 * Apply this annotation to doctrine entities that should be indexed.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Document {
    /**
     * Copy data between fields.
     *
     * @var array<\Nines\SolrBundle\Annotation\CopyField>
     */
    public array $copyField = [];

    /**
     * Indexed (or virtual) fields may be computed from other fields during
     * indexing.
     *
     * @var array<\Nines\SolrBundle\Annotation\ComputedField>
     */
    public array $computedFields = [];
}
