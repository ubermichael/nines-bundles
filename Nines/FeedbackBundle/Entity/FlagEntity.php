<?php

namespace Nines\FeedbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * FlagEntity
 *
 * @ORM\Table(name="flag_entity")
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\FlagEntityRepository")
 */
class FlagEntity extends AbstractEntity
{
    
    /**
     * A string of the form entity:id where entity is the fully-qualified class
     * name and id is the numeric id.
     * @ORM\Column(type="string", length=120)
     * @var string
     */
    private $entity;    
    
    /**
     * Flag.
     * 
     * @var Flag
     * @ORM\ManyToOne(targetEntity="Flag", inversedBy="entities")
     * @ORM\JoinColumn(name="flag_id", referencedColumnName="id", nullable=false)
     */
    private $flag;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function __toString() {
        return "{$entity}:{$flag->getLabel()}";
    }

    /**
     * Set entity
     *
     * @param string $entity
     *
     * @return FlagEntity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get entity
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set flag
     *
     * @param Flag $flag
     *
     * @return FlagEntity
     */
    public function setFlag(Flag $flag)
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * Get flag
     *
     * @return Flag
     */
    public function getFlag()
    {
        return $this->flag;
    }
}
