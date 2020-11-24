<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\MediaBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Nines\MediaBundle\Repository\CitationRepository;

/**
 * @ORM\Entity(repositoryClass=CitationRepository::class)
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"citation", "description"}, flags={"fulltext"}),
 *     @ORM\Index(columns={"entity"})
 * })
 */
class Citation extends AbstractEntity {
    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    private $entity;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $citation;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct() {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string {
        return $this->citation;
    }

    public function setEntity(AbstractEntity $entity) : void {
        if ( ! $entity->getId()) {
            throw new Exception('Citation entities must be persisted.');
        }
        $this->entity = ClassUtils::getClass($entity) . ':' . $entity->getId();
    }

    public function getEntity() : ?string {
        return $this->entity;
    }

    public function getDescription() : ?string {
        return $this->description;
    }

    public function setDescription(?string $description) : self {
        $this->description = $description;

        return $this;
    }

    public function getCitation() : ?string {
        return $this->citation;
    }

    public function setCitation(string $citation) : self {
        $this->citation = $citation;

        return $this;
    }
}
