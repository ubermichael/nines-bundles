<?php

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * Element
 *
 * @ORM\Table(
 *  name="element", 
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"name"}),
 *      @ORM\UniqueConstraint(columns={"uri"})
 *  }), 
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ElementRepository")
 */
class Element extends AbstractTerm
{
    /**
     * @var string
     * @ORM\Column(type="string", length=256, nullable=false)
     */
    private $uri;
    
    /**
     * @var Collection|Field[]
     * @ORM\OneToMany(targetEntity="Field", mappedBy="element")
     */
    private $fields;
    
    public function __toString() {
        return $this->label;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->fields = new ArrayCollection();
    }

    /**
     * Set uri
     *
     * @param string $uri
     *
     * @return Element
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }
    
    /**
     * Add field
     *
     * @param Field $field
     *
     * @return Element
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
