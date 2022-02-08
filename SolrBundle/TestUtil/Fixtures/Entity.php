<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\TestUtil\Fixtures;

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
    private $name;

    /**
     * @Solr\Field(name="sortable", type="string")
     */
    private $sortableName;

    /**
     * @var string
     *
     * ENT_QUOTES | ENT_HTML5 === 51
     * @Solr\Field(type="text", filters={"strip_tags", "html_entity_decode(51, 'UTF-8')"})
     */
    private string $content;

    /**
     * @Solr\Field(type="strings", getter="getTags(true)")
     */
    private Collection $tags;

    /**
     * @Solr\Field(type="datetime", mutator="format('Y-m-d\TH:i:sP')")
     */
    private $date;

    private float $latitude;

    private float $longitude;

    public function __construct() {
        $this->tags = new ArrayCollection();
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
