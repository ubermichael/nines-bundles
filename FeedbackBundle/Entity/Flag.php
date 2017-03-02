<?php

namespace Nines\FeedbackBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * Flag
 *
 * @ORM\Table(name="flag")
 * @ORM\Entity(repositoryClass="Nines\FeedbackBundle\Repository\FlagRepository")
 */
class Flag extends AbstractTerm {
    
    /**
     * @var Collection|FlagEntity[] 
     * @ORM\OneToMany(targetEntity="FlagEntity", mappedBy="flag")
     */
    private $entities;
    
    public function __construct() {
        parent::__construct();
        $this->entities = new ArrayCollection();        
    }
    

    /**
     * Add entity
     *
     * @param FlagEntity $entity
     *
     * @return Flag
     */
    public function addEntity(FlagEntity $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }

    /**
     * Remove entity
     *
     * @param FlagEntity $entity
     */
    public function removeEntity(FlagEntity $entity)
    {
        $this->entities->removeElement($entity);
    }

    /**
     * Get entities
     *
     * @return Collection
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
