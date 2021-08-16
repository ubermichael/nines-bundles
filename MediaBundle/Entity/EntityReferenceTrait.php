<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Nines\UtilBundle\Entity\AbstractEntity;

trait EntityReferenceTrait {
    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $entity;

    /**
     * @param AbstractEntity|string $entity
     *
     * @throws Exception
     */
    public function setEntity($entity) : self {
        if (is_string($entity)) {
            $this->entity = $entity;

            return $this;
        }
        if ( ! $entity instanceof AbstractEntity) {
            throw new Exception('setEntity expects a string or AbstractEntity.');
        }
        if ( ! $entity->getId()) {
            throw new Exception('setEntity requires entities to be persisted.');
        }
        $this->entity = ClassUtils::getClass($entity) . ':' . $entity->getId();

        return $this;
    }

    public function getEntity() : ?string {
        return $this->entity;
    }
}
