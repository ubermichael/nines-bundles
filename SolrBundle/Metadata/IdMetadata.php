<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\UtilBundle\Entity\AbstractEntity;
use ReflectionMethod;

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

    private $getterArgs;

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    public function getGetter() : string {
        return $this->getter;
    }

    public function setGetter(string $getter) : self {
        list($name, $args) = $this->parseFunctionCall($getter);
        $this->getter = $name;
        $this->getterArgs = $args;

        return $this;
    }

    public function fetch(AbstractEntity $entity) {
        if ($this->getterArgs) {
            $ref = new ReflectionMethod($entity, $this->getter);

            return $ref->invokeArgs($entity, $this->getterArgs);
        }
        $method = $this->getter;

        return $entity->{$method}();
    }
}
