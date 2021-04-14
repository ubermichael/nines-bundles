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
 */
class ParentEntity {
    /**
     * @ORM\Id
     * @Solr\Id
     */
    private $id;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;

        return $this;
    }

}
