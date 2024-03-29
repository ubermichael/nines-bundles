<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Tests\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nines\SolrBundle\Annotation as Solr;

/**
 * @ORM\Entity
 * @Solr\Document
 */
class ComplexId {
    /**
     * @var int
     * @ORM\Id
     * @Solr\Id(name="idname", getter="idGetter('abc', 1, true)")
     */
    private $id;

    public function __construct() {
        $this->id = 7;
    }

    public function getId() : int {
        return $this->id;
    }

    public function idGetter() : int {
        return $this->id;
    }
}
