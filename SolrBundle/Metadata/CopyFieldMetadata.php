<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

class CopyFieldMetadata {
    /**
     * @var array
     */
    private $from;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $solrName;

    /**
     * @var string
     */
    private $type;

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    public function getFrom() : array {
        return $this->from;
    }

    public function setFrom(array $from) : self {
        $this->from = $from;

        return $this;
    }

    public function getSolrName() : string {
        return $this->solrName;
    }

    public function setSolrName(string $solrName) : self {
        $this->solrName = $solrName;

        return $this;
    }

    public function getType() : string {
        return $this->type;
    }

    public function setType(string $type) : self {
        $this->type = $type;

        return $this;
    }
}
