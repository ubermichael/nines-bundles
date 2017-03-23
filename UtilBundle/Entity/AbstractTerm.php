<?php

namespace Nines\UtilBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An abstract term has a computer friendly name, a human readable label,
 * and a description. 
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractTerm extends AbstractEntity
{
    /**
     * Name of the status.
     * 
     * @ORM\Column(type="string", length=120)
     * @var string
     * @Groups({"shallow"})
     */
    private $name;

    /**
     * Human readable status label.
     * @var string
     * @ORM\Column(type="string", length=120)
     * @Groups({"shallow"})
     */
    private $label;
    
    /**
     * Descriptino of the status.
     * @var title
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"shallow"})
     */
    private $description;
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get the status label.
     * @return string
     */
    public function __toString() {
        return $this->label;
    }


    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

}

