<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use DateTimeInterface;

interface AbstractEntityInterface {
    public function getId() : ?int;

    public function getCreated() : DateTimeInterface;

    public function getUpdated() : DateTimeInterface;

    public function prePersist() : void;

    public function preUpdate() : void;
}
