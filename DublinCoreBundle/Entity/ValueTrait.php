<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait ValueTrait {
    /**
     * @var Collection|Value[]
     */
    protected $values;

    public function __construct() {
        $this->values = new ArrayCollection();
    }

    public function getValues() {
        return $this->values;
    }

    public function setValues($values) {
        $this->values = new ArrayCollection();
        if ( ! $values) {
            return $this;
        }

        foreach ($values as $value) {
            $this->addValue($value);
        }

        return $this;
    }

    public function addValue(Value $value) {
        $value->setEntity($this);
        $this->values[] = $value;

        return $this;
    }

    public function removeValue(Value $value) : self {
        if ($this->values->contains($value)) {
            $this->values->remove($value);
        }

        return $this;
    }
}
