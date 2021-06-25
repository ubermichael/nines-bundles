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
interface AbstractEntityInterface {

    /**
     * Force all entities to provide a stringify function.
     */
    public function __toString() : string;

    /**
     * Get the ID.
     */
    public function getId() : ?int;

    /**
     * Does nothing. Setting the created timestamp happens automatically. Exists
     * to prevent a subclass accidentally setting a timestamp.
     */
    public function setCreated(DateTimeInterface $created) : void;

    /**
     * Get the created timestamp.
     */
    public function getCreated() : DateTimeInterface;

    /**
     * Does nothing. Setting the updated timestamp happens automatically.
     */
    public function setUpdated(DateTimeInterface $updated) : void;

    /**
     * Get the updated timestamp.
     */
    public function getUpdated() : DateTimeInterface;
}
