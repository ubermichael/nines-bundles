<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

interface ValueInterface {
    public function getValues();

    public function setValues($values);

    public function addValue(Value $value);

    public function removeValue(Value $value);
}
