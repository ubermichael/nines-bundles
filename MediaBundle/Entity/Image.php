<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\MediaBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image extends AbstractImage {
    /**
     * @var string
     * @ORM\Column(type="string", length=80, nullable=false)
     */
    private $entity;

    public function __construct() {
        parent::__construct();
    }

    public function getEntity() : ?string {
        return $this->entity;
    }

    public function setEntity($entity) : self {
        if ($entity instanceof AbstractEntity) {
            $this->entity = get_class($entity) . ':' . $entity->getId();
        } else {
            $this->entity = $entity;
        }

        return $this;
    }
}
