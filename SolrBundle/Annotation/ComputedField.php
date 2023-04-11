<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

/**
 * Define virtual fields as needed.
 *
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class ComputedField {
    /**
     * Type of the data to be indexed. See Field::TYPE_MAP for a list of
     * supported types.
     *
     * @Required
     */
    public ?string $type = null;

    /**
     * Field boost, to make some fields more or less important. Defaults to 1.
     * Higher values are more important.
     */
    public ?float $boost = null;

    /**
     * Virtual property name.
     *
     * @Required
     */
    public ?string $name = null;

    /**
     * A method from the indexed entity.
     *
     * @Required
     */
    public ?string $getter = null;
}
