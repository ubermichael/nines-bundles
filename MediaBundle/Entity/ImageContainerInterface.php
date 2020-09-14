<?php


namespace Nines\MediaBundle\Entity;


use Doctrine\Common\Collections\Collection;

interface ImageContainerInterface {

    public function getId() : ?int;

    /**
     * @param Image $image
     *
     * @return mixed
     */
    public function addImage(Image $image);

    /**
     * @param Image $image
     *
     * @return mixed
     */
    public function removeImage(Image $image);

    /**
     * @param Image $image
     *
     * @return bool
     */
    public function hasImage(Image $image) : bool;

    /**
     * @param Collection|Image[] $images
     *
     * @return mixed
     */
    public function setImages($images);

    /**
     * @return Collection|Image[]
     */
    public function getImages();

}
