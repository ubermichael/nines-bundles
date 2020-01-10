<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Twig;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TextExtension extends AbstractExtension {
    /**
     * Define the filters for the extension.
     *
     * @return array|TwigFilter[]
     */
    public function getFilters() : array {
        return [
            new TwigFilter('ord', [$this, 'ord']),
            new TwigFilter('chr', [$this, 'chr']),
            new TwigFilter('class_name', [$this, 'className']),
            new TwigFilter('short_name', [$this, 'shortName']),
        ];
    }

    /**
     * Wrapper around PHP's ord() function.
     *
     * @param $str
     *
     * @return null|string|string[]
     */
    public function ord($str) {
        if ( ! $str) {
            return;
        }

        return mb_ord($str, 'UTF-8');
    }

    /**
     * Wrapper around PHP's chr() function.
     *
     * @param $int
     *
     * @return null|false|string|string[]
     */
    public function chr($int) {
        if ( ! $int) {
            return;
        }

        return mb_chr($int, 'UTF-8');
    }

    /**
     * Get the full class name of an object.
     *
     * @param mixed $object
     *
     * @throws InvalidArgumentException
     */
    public function className($object) : string {
        if ( ! is_object($object)) {
            throw new InvalidArgumentException('Expected object');
        }

        return get_class($object);
    }

    /**
     * Get the short class name of an object.
     *
     * @param object $object
     *
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function shortName($object) : string {
        if ( ! is_object($object)) {
            throw new InvalidArgumentException('Expected object');
        }

        return (new ReflectionClass($object))->getShortName();
    }
}
