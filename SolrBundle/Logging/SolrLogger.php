<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Logging;

use Solarium\Core\Query\AbstractQuery;

class SolrLogger
{
    /**
     * @var bool
     */
    private $enabled = true;

    private $requests;

    public function __construct() {
        $this->requests = [];
    }

    public function log($serverUri, $queryUri) {
        $this->requests[] = [
            'server' => $serverUri,
            'query' => $queryUri,
        ];
    }

    public function getRequests() {
        return $this->requests;
    }

    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

}
