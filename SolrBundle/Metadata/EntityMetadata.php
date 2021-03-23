<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

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
     * List of key, value pairs to add to all documents going into Solr.
     *
     * @var array
     */
    private $fixed;

    /**
     * Map of solr field name to entity field name.
     *
     * @var array
     */
    private $solrFields;

    /**
     * Map of entity field name => FieldMetadata.
     *
     * @var FieldMetadata[];
     */
    private $fieldMetadata;

    public function __construct() {
        $this->fixed = [];
        $this->solrFields = [];
        $this->fieldMetadata = [];
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

    public function getSolrFields() : array {
        return $this->solrFields;
    }

    /**
     * @return FieldMetadata[]
     */
    public function getFieldMetadata() : array {
        return $this->fieldMetadata;
    }

    /**
     * @param FieldMetadata[] $fieldMetadata
     */
    public function setFieldMetadata(array $fieldMetadata) : self {
        $this->fieldMetadata = $fieldMetadata;

        return $this;
    }

    public function addFieldMetadata(FieldMetadata $fieldMetadata) : self {
        $this->fieldMetadata[$fieldMetadata->getFieldName()] = $fieldMetadata;
        $this->solrFields[$fieldMetadata->getSolrName()] = $fieldMetadata->getFieldName();

        return $this;
    }

    public function fetch(AbstractEntity $entity) : void {
        // do nothing. doesn't make sense here.
    }
}
