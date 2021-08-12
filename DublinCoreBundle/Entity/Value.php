<?php

declare(strict_types=1);

namespace Nines\DublinCoreBundle\Entity;

use Nines\DublinCoreBundle\Repository\ValueRepository;
use Doctrine\ORM\Mapping as ORM;
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

    /**
     * @inheritDoc
     */
    public function __toString() : string {
        return $this->data;
    }

    public function __construct() {
        parent::__construct();
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getElement(): ?Element
    {
        return $this->element;
    }

    public function setElement(?Element $element): self
    {
        $this->element = $element;

        return $this;
    }

}
