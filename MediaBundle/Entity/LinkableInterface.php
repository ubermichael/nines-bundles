<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Nines\UtilBundle\Entity\AbstractEntityInterface;

interface LinkableInterface extends AbstractEntityInterface {
    /**
     * @return array<Link>
     */
    public function getLinks() : array;

    /**
     * @param array<Link>|Collection<int,Link> $links
     */
    public function setLinks($links) : self;

    public function addLink(Link $link) : self;

    public function removeLink(Link $link) : self;
}
