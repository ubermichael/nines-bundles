<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\TestUtil\Fixtures;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\SolrBundle\Annotation as Solr;

/**
 * @ORM\Entity
 * @Solr\Document(
 *     copyField=@Solr\CopyField(from={"name", "tags"}, to="content", type="texts"),
 *     computedFields=@Solr\ComputedField(name="coordinates", type="location", getter="getCoordinates")
 * )
 */
class Entity extends ParentEntity {
    /**
     * @Solr\Field(type="text", boost=2.0)
     */
    private ?string $name = null;

    /**
     * @Solr\Field(name="sortable", type="string")
     */
    private ?string $sortableName = null;

    /**
     * ENT_QUOTES | ENT_HTML5 === 51.
     *
     * @Solr\Field(type="text", filters={"strip_tags", "html_entity_decode(51, 'UTF-8')"})
     */
    private ?string $content = null;

    /**
     * @Solr\Field(type="strings", getter="getTags(true)")
     */
    private ?Collection $tags = null;

    /**
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:sP')")
     */
    private ?DateTimeInterface $date = null;

    private ?float $latitude = null;

    private ?float $longitude = null;

    public function __construct() {
        $this->tags = new ArrayCollection();
    }

    public function getContent() : ?string {
        return $this->content;
    }

    public function setContent(?string $content) : void {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) : self {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortableName() {
        return $this->sortableName;
    }

    /**
     * @param mixed $sortableName
     */
    public function setSortableName($sortableName) : self {
        $this->sortableName = $sortableName;

        return $this;
    }

    public function getTags() : Collection {
        return $this->tags;
    }

    public function setTags(Collection $tags) : self {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date) : self {
        $this->date = $date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLatitude() {
        return $this->latitude;
    }

    /**
     * @param mixed $latitude
     */
    public function setLatitude($latitude) : self {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLongitude() {
        return $this->longitude;
    }

    /**
     * @param mixed $longitude
     */
    public function setLongitude($longitude) : self {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCoordinates() : ?string {
        if ($this->latitude && $this->longitude) {
            return $this->latitude . ',' . $this->longitude;
        }

        return null;
    }
}
