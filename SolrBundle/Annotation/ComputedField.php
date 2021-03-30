<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
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
class ComputedField
{
    /**
     * Type of the data to be indexed. See Field::TYPE_MAP for a list of
     * supported types.
     *
     * @var string
     * @Required
     */
    public $type;

    /**
     * Field boost, to make some fields more or less important. Defaults to 1.
     * Higher values are more important.
     *
     * @var float
     */
    public $boost;

    /**
     * Virtual property name.
     *
     * @var string
     */
    public $name;

    /**
     * A method from the indexed entity.
     *
     * @var string
     */
    public $getter;
}
