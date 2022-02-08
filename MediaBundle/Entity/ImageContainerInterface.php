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

interface ImageContainerInterface extends AbstractEntityInterface {
    public function addImage(Image $image) : self;

    public function removeImage(Image $image) : self;

    public function containsImage(Image $image) : bool;

    /**
     * @param array<Image>|Collection<int,Image> $images
     */
    public function setImages($images) : self;

    /**
     * @return array<Image>
     */
    public function getImages() : array;
}
