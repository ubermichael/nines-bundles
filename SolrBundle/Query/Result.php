<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\Query;

use Doctrine\ORM\EntityManagerInterface;
use Solarium\Component\Result\Highlighting\Highlighting;
use Solarium\QueryType\Select\Result\Result as SolrResult;

class Result {
    /**
     * @var SolrResult
     */
    private $resultSet;

    /**
     * @var array
     */
    private $entities;

    private EntityManagerInterface $em;

    /**
     * @var ?Highlighting
     */
    private $highlighting;

    public function __construct(SolrResult $resultSet, EntityManagerInterface $em) {
        $this->resultSet = $resultSet;
        $this->em = $em;
        $this->entities = [];
        $this->highlighting = null;
    }

    public function count() {
        return $this->resultSet->count();
    }

    public function total() {
        return $this->resultSet->getNumFound();
    }

    public function getDocument($i) {
        return $this->resultSet->getDocuments()[$i];
    }

    public function getAllDocuments() {
        return $this->resultSet->getDocuments();
    }

    public function entity($i) {
        if ( ! isset($this->entities[$i])) {
            $document = $this->getDocument($i);
            list($class, $id) = explode(':', $document->id);
            $this->entities[$i] = $this->em->find($class, $id);
        }

        return $this->entities[$i];
    }

    public function hasHighlighting() {
        if ($this->highlighting) {
            return true;
        }
        $this->highlighting = $this->resultSet->getHighlighting();

        return null !== $this->highlighting;
    }

    public function getHighlighting($i) {
        if ( ! $this->hasHighlighting()) {
            return [];
        }
        $id = $this->getDocument($i)->id;

        return $this->highlighting->getResult($id);
    }

    public function getFacet($name) {
        return $this->resultSet->getFacetSet()->getFacet($name);
    }
}
