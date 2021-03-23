<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait CitationTrait
{
    /**
     * @var Citation[]|Collection
     */
    protected $Citations;

    public function __construct() {
        $this->Citations = new ArrayCollection();
    }

    public function getCitations() {
        return $this->Citations;
    }

    public function setCitations($Citations) {
        $this->Citations = new ArrayCollection();
        if ( ! $Citations) {
            return $this;
        }

        foreach ($Citations as $Citation) {
            $this->addCitation($Citation);
        }
        $this->Citations = $Citations;

        return $this;
    }

    public function addCitation(Citation $Citation) {
        $Citation->setEntity($this);
        $this->Citations[] = $Citation;

        return $this;
    }

    public function removeCitation(Citation $Citation) : self {
        if ($this->Citations->contains($Citation)) {
            $this->Citations->remove($Citation);
        }

        return $this;
    }
}
