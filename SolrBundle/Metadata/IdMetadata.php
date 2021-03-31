<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionException;
use ReflectionMethod;

/**
 * Id Metadata for a mapped entity
 */
class IdMetadata extends Metadata
{
    /**
     * Name of the ID field.
     *
     * @var string
     */
    private $name;

    /**
     * Name and params of the method to get the id.
     *
     * @var string
     */
    private $getter;

    /**
     * Arguments to pass to the getter.
     *
     * @var array
     */
    private $getterArgs;

    public function getName() : string {
        return $this->name;
    }

    /**
     * Name of the metadata field.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    /**
     * Name of the getter function.
     *
     * @return string
     */
    public function getGetter() : string {
        return $this->getter;
    }

    /**
     * Set the getter function
     *
     * @param string $getter
     *
     * @return $this
     */
    public function setGetter(string $getter) : self {
        [$name, $args] = $this->parseFunctionCall($getter);
        $this->getter = $name;
        $this->getterArgs = $args;

        return $this;
    }

    /**
     * Call the getter method for the ID and return the result.
     *
     * @param AbstractEntity $entity
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function fetch(AbstractEntity $entity) {
        if ($this->getterArgs) {
            $ref = new ReflectionMethod($entity, $this->getter);

            return $ref->invokeArgs($entity, $this->getterArgs);
        }
        $method = $this->getter;

        return $entity->{$method}();
    }
}
