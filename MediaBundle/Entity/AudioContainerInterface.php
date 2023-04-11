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

interface AudioContainerInterface extends AbstractEntityInterface {
    public function addAudio(Audio $audio) : self;

    public function removeAudio(Audio $audio) : self;

    public function containsAudio(Audio $audio) : bool;

    /**
     * @param array<Audio>|Collection<int,Audio> $audios
     */
    public function setAudios($audios) : self;

    /**
     * @return array<Audio>
     */
    public function getAudios() : array;
}
