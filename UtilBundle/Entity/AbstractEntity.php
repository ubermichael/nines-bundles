<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\UtilBundle\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Nines\SolrBundle\Annotation as Solr;
use Stringable;

/**
 * AbstractEntity adds id, created, and updated fields along with the
 * normal getters. And it sets up automatic callbacks to set the created
 * and updated DateTimes.
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity implements AbstractEntityInterface, Stringable {
    /**
     * The entity's ID.
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Solr\Id
     */
    protected ?int $id = null;

    /**
     * The DateTime the entity was created (persisted really).
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:s\Z')")
     */
    protected ?DateTimeInterface $created = null;

    /**
     * The DateTime the entity was last updated.
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:s\Z')")
     */
    protected ?DateTimeInterface $updated = null;

    /**
     * Constructor. Does nothing. Exists in case a subclass accidentally calls
     * parent::__construct().
     */
    public function __construct() {
    }

    /**
     * Get the ID.
     */
    public function getId() : ?int {
        return $this->id;
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

    public function setCreated(DateTimeInterface $created) : self {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the updated timestamp.
     */
    public function getUpdated() : DateTimeInterface {
        return $this->updated;
    }

    public function setUpdated(DateTimeInterface $updated) : self {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Sets the created and updated timestamps.
     *
     * @ORM\PrePersist
     */
    public function prePersist() : void {
        if ( ! $this->created) {
            $this->created = new DateTimeImmutable();
            $this->updated = new DateTimeImmutable();
        }
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
