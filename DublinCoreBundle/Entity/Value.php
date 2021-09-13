<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\DublinCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nines\DublinCoreBundle\Repository\ValueRepository;
use Nines\MediaBundle\Entity\EntityReferenceInterface;
use Nines\MediaBundle\Entity\EntityReferenceTrait;
use Nines\UtilBundle\Entity\AbstractEntity;

/**
 * @ORM\Entity(repositoryClass=ValueRepository::class)
 * @ORM\Table(name="dc_value", indexes={
 *     @ORM\Index(columns={"data"}, flags={"fulltext"})
 * })
 */
class Value extends AbstractEntity implements EntityReferenceInterface {
    use EntityReferenceTrait;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $data;

    /**
     * @var Element
     * @ORM\ManyToOne(targetEntity="Element", inversedBy="values")
     */
    private $element;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString() : string {
        return $this->data;
    }

    public function getData() : ?string {
        return $this->data;
    }

    public function setData(string $data) : self {
        $this->data = $data;

        return $this;
    }

    public function getElement() : ?Element {
        return $this->element;
    }

    public function setElement(?Element $element) : self {
        $this->element = $element;

        return $this;
    }
}
