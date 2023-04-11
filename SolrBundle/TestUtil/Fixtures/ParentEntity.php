<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\TestUtil\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use Nines\SolrBundle\Annotation as Solr;

/**
 * @ORM\Entity
 */
class ParentEntity {
    /**
     * @ORM\Id
     * @Solr\Id
     */
    private ?int $id = null;

    /**
     * @Solr\Field(type="string")
     */
    private ?string $something = null;

    public function getId() : ?int {
        return $this->id;
    }

    public function setId(int $id) : self {
        $this->id = $id;

        return $this;
    }

    public function getSomething() : ?string {
        return $this->something;
    }

    public function setSomething(string $something) : self {
        $this->something = $something;

        return $this;
    }
}
