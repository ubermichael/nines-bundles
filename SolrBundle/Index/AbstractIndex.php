<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Index;

use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Query\QueryBuilder;
use Nines\SolrBundle\Services\SolrManager;

/**
 * Generic parent class for index classes. They're like Doctrine repositories,
 * but meant for the solr search index.
 */
abstract class AbstractIndex {
    protected ?SolrManager $manager = null;

    /**
     * Build and return a query builder.
     *
     * @throws NotConfiguredException
     */
    protected function createQueryBuilder() : QueryBuilder {
        return $this->manager->createQueryBuilder();
    }

    /**
     * @required
     *
     * @codeCoverageIgnore
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }
}
