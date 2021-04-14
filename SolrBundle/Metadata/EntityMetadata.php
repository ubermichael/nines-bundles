<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

use Nines\SolrBundle\Annotation\Field;

/**
 * Collection of Solr metadata for an entity, as defined by the annotations.
 */
class EntityMetadata extends Metadata {
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
     * @var CopyFieldMetadata[]
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

    /**
     * EntityMetadata constructor.
     */
    public function __construct() {
        $this->fixed = [];
        $this->fieldMetadata = [];
        $this->copyFields = [];
    }

    /**
     * Find the ID metadata for the entity.
     */
    public function getId() : IdMetadata {
        return $this->id;
    }

    /**
     * Set the ID metadata for the entity.
     *
     * @return $this
     */
    public function setId(IdMetadata $id) : self {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the FQCN for the entity managed by this metadata.
     */
    public function getClass() : string {
        return $this->class;
    }

    /**
     * Set the FQCN for the entity managed by this metadata.
     *
     * @return $this
     */
    public function setClass(string $class) : self {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the fixed fields for this entity.
     */
    public function getFixed() : array {
        return $this->fixed;
    }

    /**
     * Set the fixed fields for this entity.
     *
     * @return $this
     */
    public function setFixed(array $fixed) : self {
        $this->fixed = $fixed;

        return $this;
    }

    /**
     * Add a fixed field to the metadata. It will be added to every search
     * document that is indexed.
     *
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function addFixed($name, $value) : self {
        $this->fixed[$name] = $value;

        return $this;
    }

    /**
     * Get all the field metadata.
     *
     * @return FieldMetadata[]
     */
    public function getFieldMetadata() : array {
        return $this->fieldMetadata;
    }

    /**
     * Add a field metadata for the entity.
     *
     * @return $this
     */
    public function addFieldMetadata(FieldMetadata $fieldMetadata) : self {
        $this->fieldMetadata[$fieldMetadata->getFieldName()] = $fieldMetadata;

        return $this;
    }

    /**
     * Add a copy field. During indexing data from the fields named in $from
     * will be copied to an array in $to. The field will be named $name, which
     * is what the index methods should use to query it.
     */
    public function addCopyField(CopyFieldMetadata $fieldMetadata) : void {
        $this->copyFields[] = $fieldMetadata;
    }

    /**
     * Get the copy fields.
     *
     * @return array
     */
    public function getCopyFields() {
        return $this->copyFields;
    }
}
