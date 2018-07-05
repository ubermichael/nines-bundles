<?php

namespace Nines\UtilBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * AbstractEntity adds id, created, and updated fields along with the
 * normal getters. And it sets up automatic callbacks to set the created
 * and updated DateTimes.
 *
 * @ORM\MappedSuperclass
 */
abstract class AbstractEntity
{
    /**
     * The entity's ID.
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"shallow"})
     */
    protected $id;

    /**
     * The DateTime the entity was created (persisted really).
     *
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"shallow"})
     */
    protected $created;

    /**
     * The DateTime the entity was last updated.
     *
     * @var DateTime
     * @ORM\Column(type="datetime")
     * @Groups({"shallow"})
     */
    protected $updated;

    /**
     * Constructor. Does nothing. Exists incase a subclass accidentally calls
     * parent::__construct().
     */
    public function __construct() {
        $this->created = new DateTime();
        $this->updated = new DateTime();
    }

    /**
     * Get the ID.
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Does nothing. Setting the created timestamp happens automatically. Exists
     * to prevent a subclass accidentally setting a timestamp.
     *
     * @return void
     */
    public function setCreated(DateTime $created) {
        $this->created = $created;
    }

    /**
     * Get the created timestamp.
     *
     * @return DateTime
     */
    public final function getCreated() {
        return $this->created;
    }

    /**
     * Does nothing. Setting the updated timestamp happens automatically.
     *
     * @return void
     */
    function setUpdated(DateTime $updated) {
        $this->updated = $updated;
    }

    /**
     * Get the updated timestamp.
     *
     * @return DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Sets the created and updated timestamps. This method should be
     * private or protected, but that interferes with the life cycle callbacks.
     *
     * @ORM\PrePersist()
     *
     * @return void
     */
    public final function prePersist() {
        if( ! isset($this->created)) {
            $this->created = new DateTime();
            $this->updated = new DateTime();
        }
    }

    /**
     * Sets the updated timestamp.
     *
     * @ORM\PreUpdate()
     *
     * @return void
     */
    public final function preUpdate() {
        $this->updated = new DateTime();
    }

    /**
     * Force all entities to provide a stringify function.
     *
     * @return string
     */
    abstract public function __toString();

}
