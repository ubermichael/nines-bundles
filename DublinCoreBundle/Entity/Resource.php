<?php

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Resource
 * @ORM\MappedSuperclass
 */
class Resource extends AbstractEntity {

    /**
     * @var Collection|Field[]
     * @ORM\OneToMany(targetEntity="Field", mappedBy="resource")
     */
    private $fields;

    public function __toString() {
        foreach($this->fields as $field) {
            if($field->getName() === 'dc_title') {
                return $field->getValue();
            }
        }
        return '(untitled)';
    }

    public function __construct() {
        parent::__construct();
        $this->fields = new ArrayCollection();
    }

    /**
     * Add field
     *
     * @param Field $field
     *
     * @return Resource
     */
    public function addField(Field $field)
    {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove field
     *
     * @param Field $field
     */
    public function removeField(Field $field)
    {
        $this->fields->removeElement($field);
    }

    /**
     * Get fields
     *
     * @return Collection
     */
    public function getFields()
    {
        return $this->fields;
    }
}
