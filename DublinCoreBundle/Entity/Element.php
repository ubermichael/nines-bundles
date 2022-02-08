<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\UtilBundle\Entity\AbstractTerm;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Element.
 *
 * @ORM\Table(name="nines_dc_element", uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"uri"})
 * })
 * @ORM\Entity(repositoryClass="Nines\DublinCoreBundle\Repository\ElementRepository")
 */
class Element extends AbstractTerm {
    /**
     * @ORM\Column(type="string", length=190, nullable=false)
     * @Assert\Url
     * @Assert\NotBlank
     */
    private ?string $uri = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $comment = null;

    /**
     * @var Collection<int,Value>|Value[]
     * @ORM\OneToMany(targetEntity="Value", mappedBy="element")
     */
    private $values;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->values = new ArrayCollection();
    }

    /**
     * Set uri.
     *
     * @codeCoverageIgnore
     */
    public function setUri(string $uri) : self {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get uri.
     *
     * @codeCoverageIgnore
     */
    public function getUri() : ?string {
        return $this->uri;
    }

    /**
     * Set comment.
     *
     * @codeCoverageIgnore
     */
    public function setComment(?string $comment) : self {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @codeCoverageIgnore
     */
    public function getComment() : ?string {
        return $this->comment;
    }

    /**
     * @return Collection<int,Value>|Value[]
     * @codeCoverageIgnore
     */
    public function getValues() : Collection {
        return $this->values;
    }

    public function addValue(Value $value) : self {
        if ( ! $this->values->contains($value)) {
            $this->values[] = $value;
            $value->setElement($this);
        }

        return $this;
    }

    public function removeValue(Value $value) : self {
        if ($this->values->removeElement($value)) {
            // set the owning side to null (unless already changed)
            if ($value->getElement() === $this) {
                $value->setElement(null);
            }
        }

        return $this;
    }
}
