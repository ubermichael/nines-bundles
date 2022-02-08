<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;

trait ValueTrait {
    /**
     * @var Collection<int,Value>|Value[]
     */
    protected $values;

    public function __construct() {
        $this->values = new ArrayCollection();
    }

    /**
     * @return Collection<int,Value>|Value[]
     */
    public function getValues(?string $name = null) {
        if ($name) {
            return $this->values->filter(fn(Value $v) => $v->getElement()->getName() === $name);
        }

        return $this->values;
    }

    /**
     * @param null|array<Value>|Collection<int,Value> $values
     *
     * @throws Exception
     */
    public function setValues($values = []) : self {
        $this->values = new ArrayCollection();
        if ( ! $values) {
            return $this;
        }

        foreach ($values as $value) {
            $this->addValue($value);
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    public function addValue(Value $value) : self {
        $value->setEntity($this);
        $this->values[] = $value;

        return $this;
    }

    public function removeValue(Value $value) : self {
        if ($this->values->contains($value)) {
            $idx = $this->values->indexOf($value);
            $this->values->remove($idx);
        }

        return $this;
    }
}
