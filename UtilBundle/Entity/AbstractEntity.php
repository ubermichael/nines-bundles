<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Nines\SolrBundle\Annotation as Solr;

/**
 * AbstractEntity adds id, created, and updated fields along with the
 * normal getters. And it sets up automatic callbacks to set the created
 * and updated DateTimes.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity implements AbstractEntityInterface {
    /**
     * The entity's ID.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Solr\Id
     */
    protected $id;

    /**
     * The DateTime the entity was created (persisted really).
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime_immutable")
     *
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:s\Z')")
     */
    protected $created;

    /**
     * The DateTime the entity was last updated.
     *
     * @var DateTimeInterface
     * @ORM\Column(type="datetime_immutable")
     *
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:s\Z')")
     */
    protected $updated;

    /**
     * Constructor. Does nothing. Exists in case a subclass accidentally calls
     * parent::__construct().
     */
    public function __construct() {
    }

    /**
     * Force all entities to provide a stringify function.
     */
    abstract public function __toString() : string;

    /**
     * Get the ID.
     */
    public function getId() : ?int {
        return $this->id;
    }

    /**
     * Does nothing. Setting the created timestamp happens automatically. Exists
     * to prevent a subclass accidentally setting a timestamp.
     */
    public function setCreated(DateTimeInterface $created) : void {
    }

    /**
     * Get the created timestamp.
     */
    public function getCreated() : DateTimeInterface {
        if ( ! $this->created) {
            return new DateTimeImmutable();
        }

        return $this->created;
    }

    /**
     * Does nothing. Setting the updated timestamp happens automatically.
     */
    public function setUpdated(DateTimeInterface $updated) : void {
    }

    /**
     * Get the updated timestamp.
     */
    public function getUpdated() : DateTimeInterface {
        return $this->updated;
    }

    /**
     * Sets the created and updated timestamps.
     *
     * @ORM\PrePersist
     */
    public function prePersist() : void {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
    }

    /**
     * Sets the updated timestamp.
     *
     * @ORM\PreUpdate
     */
    public function preUpdate() : void {
        $this->updated = new DateTimeImmutable();
    }
}
