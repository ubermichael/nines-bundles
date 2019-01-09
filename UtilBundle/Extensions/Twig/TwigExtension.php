<?php

namespace Nines\UtilBundle\Extensions\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Add some class reflection methods to Twig. 
 */
class TwigExtension extends AbstractExtension {
    
    /**
     * {@inheritdoc}
     */
    public function getFilters() {
        return array(
            new TwigFilter('class_name', array($this, 'classFilter')),
            new TwigFilter('short_name', array($this, 'shortFilter')),
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
