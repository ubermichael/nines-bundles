<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait ReferenceableTrait {

    /**
     * @var Collection|Reference[]
     */
    protected $references;

    public function __construct() {
        $this->references = new ArrayCollection();
    }

    public function getReferences() {
        return $this->references;
    }

    public function setReferences($references) {
        $this->references = new ArrayCollection();
        if ( ! $references) {
            return $this;
        }

        foreach ($references as $reference) {
            $this->addReference($reference);
        }
        $this->references = $references;

        return $this;
    }

    public function addReference(Reference $reference) {
        $reference->setEntity($this);
        $this->references[] = $reference;

        return $this;
    }

    public function removeReference(Reference $reference) : self {
        if ($this->references->contains($reference)) {
            $this->references->remove($reference);
        }

        return $this;
    }
}
