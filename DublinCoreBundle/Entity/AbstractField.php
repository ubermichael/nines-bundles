<?php

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * Field
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractField extends AbstractEntity
{
    
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $value;
    
    /**
     * @var Element
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="fields")
     * @ORM\JoinColumn(nullable=false)
     */
    private $element;
        
    public function __toString() {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return AbstractField
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set element
     *
     * @param Element $element
     *
     * @return AbstractField
     */
    public function setElement(Element $element = null)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Get element
     *
     * @return Element
     */
    public function getElement()
    {
        return $this->element;
    }
}
