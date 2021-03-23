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
 * @Target({"PROPERTY"})
 */
class Field {
    public const TYPE_MAP = [
        'boolean' => '_b',
        'date' => '_dt',
        'datetime' => '_dt',
        'double' => '_d',
        'float' => '_f',
        'integer' => '_i',
        'long' => '_l',
        'string' => '_s',
        'text' => '_t',

        'booleans' => '_bs',
        'dates' => '_dts',
        'datetimes' => '_dts',
        'doubles' => '_ds',
        'floats' => '_fs',
        'integers' => '_is',
        'longs' => '_ls',
        'strings' => '_ss',
        'texts' => '_txt',
    ];

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
     */
    public $name;

    /**
     * A method from the indexed entity.
     *
     * @var string
     */
    public $getter;

    /**
     * A callable function on the field's object. For dates this could be
     * format('Y-m-d').
     *
     * @var string
     */
    public $mutator;

    /**
     * @var array
     */
    public $filters;
}
