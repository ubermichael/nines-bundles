<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

trait AudioContainerTrait {
    /**
     * @var Audio[]|Collection
     */
    protected $audios;

    protected function __construct() {
        $this->audios = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function addAudio(Audio $audio) {
        if ( ! $this->audios->contains($audio)) {
            $this->audios[] = $audio;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function removeAudio(Audio $audio) {
        if ($this->audios->contains($audio)) {
            $this->audios->removeElement($audio);
        }

        return $this;
    }

    public function containsAudio(Audio $audio) : bool {
        return $this->audios->contains($audio);
    }

    /**
     * @param Audio[]|Collection $audios
     *
     * @return mixed
     */
    public function setAudios($audios) {
        if (is_array($audios)) {
            $this->audios = new ArrayCollection($audios);
        } else {
            $this->audios = $audios;
        }

        return $this;
    }

    /**
     * @return Audio[]
     */
    public function getAudios() {
        return $this->audios->toArray();
    }
}
