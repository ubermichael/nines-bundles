<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Annotation;

/**
 * Use @Field() to configure the indexing for each field to be searched.
 *
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Field
{
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
        'location' => '_p',

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
     * Entity property name.
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

    /**
     * A callable function on the field's object. For dates this could be
     * format('Y-m-d').
     *
     * @var string
     */
    public $mutator;

    /**
     * List of functions to call. The data returned by getter or mutator will
     * be passed as the first argument to each function.
     *
     * @var array
     */
    public $filters;
}
