<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait AudioContainerTrait {
    /**
     * @var Audio[]|Collection<int,Audio>
     */
    protected ?Collection $audios = null;

    protected function __construct() {
        $this->audios = new ArrayCollection();
    }

    public function addAudio(Audio $audio) : self {
        if ( ! $this->audios->contains($audio)) {
            $this->audios[] = $audio;
        }

        return $this;
    }

    public function removeAudio(Audio $audio) : self {
        if ($this->audios->contains($audio)) {
            $this->audios->removeElement($audio);
        }

        return $this;
    }

    public function containsAudio(Audio $audio) : bool {
        return $this->audios->contains($audio);
    }

    /**
     * @param array<Audio>|Collection<int,Audio> $audios
     */
    public function setAudios($audios) : self {
        if (is_array($audios)) {
            $this->audios = new ArrayCollection($audios);
        } else {
            $this->audios = $audios;
        }

        return $this;
    }

    /**
     * @return array<Audio>
     */
    public function getAudios() : array {
        return $this->audios->toArray();
    }
}
