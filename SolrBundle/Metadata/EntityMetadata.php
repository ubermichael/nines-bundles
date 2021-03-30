<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\SolrBundle\Annotation\Field;
use Nines\UtilBundle\Entity\AbstractEntity;

class EntityMetadata extends Metadata
{
    /**
     * @var IdMetadata
     */
    private $id;

    /**
     * The FQCN of the entity.
     *
     * @var string
     */
    private $class;

    /**
     * List of ['from' => [], 'to' => ''].
     *
     * @var array
     */
    private $copyFields;

    /**
     * List of key, value pairs to add to all documents going into Solr.
     *
     * @var array
     */
    private $fixed;

    /**
     * Map of entity field name => FieldMetadata.
     *
     * @var FieldMetadata[];
     */
    private $fieldMetadata;

    public function __construct() {
        $this->fixed = [];
        $this->fieldMetadata = [];
        $this->copyFields = [];
    }

    public function getId() : IdMetadata {
        return $this->id;
    }

    public function setId(IdMetadata $id) : self {
        $this->id = $id;

        return $this;
    }

    public function getClass() : string {
        return $this->class;
    }

    public function setClass(string $class) : self {
        $this->class = $class;

        return $this;
    }

    public function getFixed() : array {
        return $this->fixed;
    }

    public function setFixed(array $fixed) : self {
        $this->fixed = $fixed;

        return $this;
    }

    public function addFixed($name, $value) : self {
        $this->fixed[$name] = $value;

        return $this;
    }

    /**
     * @return FieldMetadata[]
     */
    public function getFieldMetadata() : array {
        return $this->fieldMetadata;
    }

    public function addFieldMetadata(FieldMetadata $fieldMetadata) : self {
        $this->fieldMetadata[$fieldMetadata->getFieldName()] = $fieldMetadata;

        return $this;
    }

    public function addCopyFields($from, $virtual, $to) : void {
        $this->copyFields[$virtual] = [
            'to' => $to,
            'from' => $from,
        ];
    }

    public function getCopyFields() {
        return $this->copyFields;
    }

    public function fetch(AbstractEntity $entity) : void {
        // do nothing. doesn't make sense here.
    }
}
