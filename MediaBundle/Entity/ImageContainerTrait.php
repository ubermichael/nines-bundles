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

trait ImageContainerTrait {
    /**
     * @var Collection|Image[]
     */
    protected $images;

    protected function __construct() {
        $this->images = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function addImage(Image $image) {
        if ( ! $this->images->contains($image)) {
            $this->images[] = $image;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function removeImage(Image $image) {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    public function containsImage(Image $image) : bool {
        return $this->images->contains($image);
    }

    /**
     * @param Collection|Image[] $images
     *
     * @return mixed
     */
    public function setImages($images) {
        if (is_array($images)) {
            $this->images = new ArrayCollection($images);
        } else {
            $this->images = $images;
        }

        return $this;
    }

    /**
     * @return Image[]
     */
    public function getImages() {
        return $this->images->toArray();
    }
}
