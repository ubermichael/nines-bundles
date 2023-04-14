<?php

declare(strict_types=1);

namespace Nines\UtilBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass]
abstract class AbstractEntity implements Stringable {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(name: 'created', type: Types::DATETIME_IMMUTABLE)]
    protected ?DateTimeImmutable $created = null;

    #[ORM\Column(name: 'updated', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $updated = null;

    public function __construct() {
    }

    abstract public function __toString();

    public function getId() : ?int {
        return $this->id;
    }

    public function getCreated() : ?DateTimeImmutable {
        return $this->created;
    }

    #[ORM\PrePersist]
    public function setCreated() : self {
        $this->created = new DateTimeImmutable();

        return $this;
    }

    public function getUpdated() : ?DateTimeImmutable {
        return $this->updated;
    }

    #[ORM\PreUpdate]
    public function setUpdated() : self {
        $this->updated = new DateTimeImmutable();

        return $this;
    }
}
