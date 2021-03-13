<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Mapper;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Nines\SolrBundle\Metadata\EntityMetadata;
use Nines\UtilBundle\Entity\AbstractEntity;
use Solarium\QueryType\Select\Result\Document;

class EntityMapper {
    /**
     * @var EntityManagerInterface;
     */
    private $em;

    /**
     * @var EntityMetadata[]
     */
    private $map;

    public function __construct() {
        $this->map = [];
    }

    public function addEntity(EntityMetadata $entityMetadata) : void {
        $this->map[$entityMetadata->getClass()] = $entityMetadata;
    }

    public function toDocument(?AbstractEntity $entity) {
        if ( ! $entity) {
            return null;
        }
        $class = ClassUtils::getClass($entity);
        if ( ! ($entityMeta = ($this->map[$class] ?? null))) {
            return null;
        }

        $data = array_merge([
            'class_s' => $entityMeta->getClass(),
            'id' => $entityMeta->getClass() . ':' . $entityMeta->getId()->fetch($entity),
        ], $entityMeta->getFixed());

        foreach ($entityMeta->getFieldMetadata() as $fieldMetadata) {
            $data[$fieldMetadata->getSolrName()] = $fieldMetadata->fetch($entity);
        }

        return $data;
    }

    public function toEntity(Document $document) {
        list($class, $id) = explode(':', $document->id);
        if ( ! class_exists($class)) {
            throw new Exception("Unknown class: {$class}");
        }

        return $this->em->find($class, $id);
    }

    public function setEntityManager(EntityManagerInterface $em) : void {
        $this->em = $em;
    }

    public function getEntityMetadata($class) {
        if ( ! isset($this->map[$class])) {
            return;
        }

        return $this->map[$class];
    }

    public function getClasses() {
        return array_keys($this->map);
    }
}
