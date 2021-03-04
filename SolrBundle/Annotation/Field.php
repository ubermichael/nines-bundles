<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
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
     * @var string
     */
    public $name;

    /**
     * A method from the indexed entity, called to get a string representation
     * the indexed field.
     *
     * @var string
     */
    public $getter;

    /**
     * A callable function that returns a string. The data will be passed
     * as the first/only argument and the data returned will be indexed.
     *
     * @var string
     */
    public $filter;
}
