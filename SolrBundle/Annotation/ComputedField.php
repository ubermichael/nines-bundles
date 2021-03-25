<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
class ComputedField
{
    /**
     * @var string
     * @Required
     */
    public $type;

    /**
     * @var float
     */
    public $boost;

    /**
     * @var string
     * @Required
     */
    public $name;

    /**
     * A method from the indexed entity.
     *
     * @var string
     */
    public $getter;
}
