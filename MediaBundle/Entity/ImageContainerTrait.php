<?php


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
     * @param Image $image
     *
     * @return mixed
     */
    public function addImage(Image $image) {
        if( ! $this->images->contains($image)) {
            $this->images[] = $image;
        }
    }

    /**
     * @param Image $image
     *
     * @return mixed
     */
    public function removeImage(Image $image) {
        if( $this->images->contains($image)) {
            $this->images->removeElement($image);
        }
    }

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function hasImage(Image $image) : bool {
        return $this->images->contains($image);
    }

    /**
     * @param Collection|Image[] $images
     *
     * @return mixed
     */
    public function setImages($images) {
        if(is_array($images)) {
            $this->images = new ArrayCollection($images);
        } else {
            $this->images = $images;
        }
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages() {
        return $this->images->toArray();
    }

}
