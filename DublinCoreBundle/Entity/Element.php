<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;

/**
 * Element.
 *
 * @ORM\Table(
 *     name="element",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"uri"})
 *     }),
 *     @ORM\Entity(repositoryClass="Nines\DublinCoreBundle\Repository\ElementRepository")
 */
class Element extends AbstractTerm {
    /**
     * @var string
     * @ORM\Column(type="string", length=190, nullable=false)
     */
    private $uri;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $comment;

    /**
     * @var AbstractField[]|Collection
     * @ORM\OneToMany(targetEntity="AbstractField", mappedBy="element")
     */
    private $fields;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->fields = new ArrayCollection();
    }

    public function __toString() : string {
        return parent::__toString();
    }

    /**
     * Set uri.
     *
     * @param string $uri
     *
     * @return Element
     */
    public function setUri($uri) {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri.
     *
     * @return string
     */
    public function getUri() {
        return $this->uri;
    }

    /**
     * Add field.
     *
     * @return Element
     */
    public function addField(AbstractField $field) {
        $this->fields[] = $field;

        return $this;
    }

    /**
     * Remove field.
     */
    public function removeField(AbstractField $field) : void {
        $this->fields->removeElement($field);
    }

    /**
     * Get fields.
     *
     * @return Collection
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return Element
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }
}
