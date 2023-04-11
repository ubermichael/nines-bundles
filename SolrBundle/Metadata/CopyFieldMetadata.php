<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Metadata;

class CopyFieldMetadata {
    /**
     * @var array<int,string>
     */
    private ?array $from = null;

    private ?string $name = null;

    private ?string $solrName = null;

    private ?string $type = null;

    public function getName() : string {
        return $this->name;
    }

    public function setName(string $name) : self {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<int,string>
     */
    public function getFrom() : array {
        return $this->from;
    }

    /**
     * @param array<int,string> $from
     */
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
