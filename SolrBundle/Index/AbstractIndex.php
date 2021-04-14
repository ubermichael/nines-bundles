<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Index;

use Nines\SolrBundle\Query\QueryBuilder;
use Nines\SolrBundle\Services\SolrManager;

/**
 * Generic parent class for index classes. They're like Doctrine repositories,
 * but meant for the solr search index.
 */
abstract class AbstractIndex {
    protected SolrManager $manager;

    /**
     * Build and return a query builder.
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder() {
        return $this->manager->createQueryBuilder();
    }

    /**
     * @required
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }
}
