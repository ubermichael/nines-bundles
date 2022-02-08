<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionException;
use ReflectionMethod;

/**
 * Id Metadata for a mapped entity.
 */
class IdMetadata extends Metadata {
    /**
     * Name of the ID field.
     */
    private ?string $name = null;

    /**
     * Name and params of the method to get the id.
     */
    private ?string $getter = null;

    /**
     * Arguments to pass to the getter.
     *
     * @var array<int,string>
     */
    private array $getterArgs = [];

    public function getName() : string {
        return $this->name;
    }

    /**
     * Name of the metadata field.
     *
     * @return $this
     */
    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    /**
     * Name of the getter function.
     */
    public function getGetter() : string {
        return $this->getter;
    }

    /**
     * @return array<int,string>
     */
    public function getGetterArgs() : array {
        return $this->getterArgs;
    }

    /**
     * Set the getter function.
     */
    public function setGetter(string $getter) : self {
        list($name, $args) = $this->parseFunctionCall($getter);
        $this->getter = $name;
        $this->getterArgs = $args;

        return $this;
    }

    /**
     * Call the getter method for the ID and return the result.
     *
     * @throws ReflectionException
     *
     * @return mixed
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
