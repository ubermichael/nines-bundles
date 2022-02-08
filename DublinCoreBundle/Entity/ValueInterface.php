<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface ValueInterface {
    /**
     * @return Collection<int,Value>|Value[]
     */
    public function getValues(?string $name = null);

    /**
     * @param array<Value>|Collection<int,Value> $values
     */
    public function setValues($values) : self;

    public function addValue(Value $value) : self;

    public function removeValue(Value $value) : self;
}
