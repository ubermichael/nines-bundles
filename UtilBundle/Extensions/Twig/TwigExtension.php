<?php

namespace Nines\UtilBundle\Extensions\Twig;

use InvalidArgumentException;
use ReflectionClass;
use \Twig_Extension;
use \Twig_SimpleFilter;

/**
 * Add some class reflection methods to Twig. 
 *
 * @author mjoyce
 */
class TwigExtension extends Twig_Extension {
    
    /**
     * {@inheritDocs}
     */
    public function getFilters() {
        return array(
            new Twig_SimpleFilter('class_name', array($this, 'classFilter')),
            new Twig_SimpleFilter('short_name', array($this, 'shortFilter')),
        );
    }
    
    /**
     * Get the full class name of an object.
     * 
     * @param object $object
     * @return string
     * @throws InvalidArgumentException
     */
    public function classFilter($object) {
        if( ! is_object($object)) {
            throw new InvalidArgumentException("Expected object");
        }
        return get_class($object);
    }

    /**
     * Get the short class name of an object.
     * 
     * @param object $object
     * @return string
     * @throws InvalidArgumentException
     */
    public function shortFilter($object) {
        if( ! is_object($object)) {
            throw new InvalidArgumentException("Expected object");
        }
        return (new ReflectionClass($object))->getShortName();

    }
}
