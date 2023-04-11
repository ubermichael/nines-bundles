<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Exception;

interface ValueInterface {
    /**
     * @return Collection<int,Value>|Value[]
     */
    public function getValues(?string $name = null);

    /**
     * @param null|array<Value>|Collection<int,Value> $values
     *
     * @throws Exception
     */
    public function setValues($values = []) : self;

    /**
     * @throws Exception
     */
    public function addValue(Value $value) : self;

    public function removeValue(Value $value) : self;
}
