<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Repository;

use Nines\SolrBundle\Services\SolrManager;

abstract class SolrRepository
{
    protected SolrManager $manager;

    protected function createQueryBuilder() {
        return $this->manager->createQueryBuilder();
    }

    /**
     * @param SolrManager $manager
     * @required
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }
}
