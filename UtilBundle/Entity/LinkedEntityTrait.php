<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;

trait LinkedEntityTrait {
    /**
     * A string of the form entity:id where entity is the un-namespaced
     * class name in lowercase and id is the numeric id.
     *
     * @ORM\Column(type="string", length=120)
     */
    private ?string $entity = null;

    public function getEntity() : ?string {
        return $this->entity;
    }

    /**
     * @param AbstractEntityInterface|string $entity
     *
     * @throws Exception
     */
    public function setEntity($entity) : self {
        if (is_string($entity)) {
            $this->entity = $entity;
        } elseif ($entity instanceof AbstractEntity) {
            $class = ClassUtils::getClass($entity);
            $id = $entity->getId();
            if ( ! $id) {
                throw new Exception('Cannot link non-persisted entities.');
            }
            $this->entity = $class . ':' . $id;
        } else {
            throw new Exception('Cannot link ' . ClassUtils::getClass($entity));
        }

        return $this;
    }
}
