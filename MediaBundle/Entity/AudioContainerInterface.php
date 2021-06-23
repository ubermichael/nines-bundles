<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface AudioContainerInterface {
    public function getId() : ?int;

    /**
     * @return mixed
     */
    public function addAudio(Audio $audio);

    /**
     * @return mixed
     */
    public function removeAudio(Audio $audio);

    public function containsAudio(Audio $audio) : bool;

    /**
     * @param Audio[]|Collection $audios
     *
     * @return mixed
     */
    public function setAudios($audios);

    /**
     * @return Audio[]|Collection
     */
    public function getAudios();
}
