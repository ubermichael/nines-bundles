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
 * @Target({"CLASS"})
 */
class Document
{
    /**
     * @var array<Nines\SolrBundle\Annotation\CopyField>
     */
    public $copyField = [];

    /**
     * @var array<Nines\SolrBundle\Annotation\ComputedField>
     */
    public $computedFields = [];

//    public function __construct() {
//        dump(func_get_args());
//    }
}
