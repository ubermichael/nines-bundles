<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Exception;
use Nines\UtilBundle\Entity\AbstractEntity;

interface EntityReferenceInterface {
    /**
     * @throws Exception
     */
    public function setEntity(AbstractEntity $entity) : self;

    public function getEntity() : ?string;
}
