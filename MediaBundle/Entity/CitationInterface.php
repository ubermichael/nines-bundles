<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

interface CitationInterface {
    public function getCitations();

    public function setCitations($Citations);

    public function addCitation(Citation $Citation);

    public function removeCitation(Citation $Citation);
}
