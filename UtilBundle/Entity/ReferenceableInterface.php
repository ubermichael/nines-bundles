<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

interface ReferenceableInterface {
    public function getReferences();

    public function setReferences($references);

    public function addReference(Reference $reference);

    public function removeReference(Reference $reference);
}
