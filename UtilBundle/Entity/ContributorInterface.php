<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use DateTimeInterface;

interface ContributorInterface extends AbstractEntityInterface {
    /**
     * @return null|array<string,mixed>
     */
    public function getContributions() : ?array;

    /**
     * @param array<string,mixed> $contributions
     */
    public function setContributions(array $contributions) : self;

    public function addContribution(DateTimeInterface $date, string $name) : self;
}
