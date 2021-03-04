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

trait LinkableTrait {
    /**
     * @var Collection|Link[]
     */
    protected $links;

    public function __construct() {
        $this->links = new ArrayCollection();
    }

    public function getLinks() {
        return $this->links;
    }

    public function setLinks($links) {
        $this->links = new ArrayCollection();
        if ( ! $links) {
            return $this;
        }

        foreach ($links as $link) {
            $this->addLink($link);
        }
        $this->links = $links;

        return $this;
    }

    public function addLink(Link $link) {
        $link->setEntity($this);
        $this->links[] = $link;

        return $this;
    }

    public function removeLink(Link $link) : self {
        if ($this->links->contains($link)) {
            $this->links->remove($link);
        }

        return $this;
    }
}
