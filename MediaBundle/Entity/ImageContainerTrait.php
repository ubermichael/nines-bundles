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

trait ImageContainerTrait {
    /**
     * @var Collection<int,Image>|Image[]
     */
    protected $images;

    protected function __construct() {
        $this->images = new ArrayCollection();
    }

    public function addImage(Image $image) : self {
        if ( ! $this->images->contains($image)) {
            $this->images[] = $image;
        }

        return $this;
    }

    public function removeImage(Image $image) : self {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
        }

        return $this;
    }

    public function containsImage(Image $image) : bool {
        return $this->images->contains($image);
    }

    /**
     * @param array<Image>|Collection<int,Image> $images
     */
    public function setImages($images) : self {
        if (is_array($images)) {
            $this->images = new ArrayCollection($images);
        } else {
            $this->images = $images;
        }

        return $this;
    }

    /**
     * @return array<Image>
     */
    public function getImages() : array {
        return $this->images->toArray();
    }
}
