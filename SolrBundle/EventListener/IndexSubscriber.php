<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Nines\SolrBundle\Mapper\EntityMapper;
use Solarium\Client;

/**
 * The index subscriber listens for changes to indexed entities. When the
 * changes are flused in the entity manager, they are also flushed to the solr
 * index.
 */
class IndexSubscriber implements EventSubscriber {
    /**
     * List of Solr document IDs to remove.
     *
     * @var array<string>
     */
    private $removed;

    /**
     * List of Solr documents to update.
     *
     * @var array
     */
    private $updated;

    /**
     * @var EntityMapper
     */
    private $mapper;

    private Client $client;

    /**
     * Build the subscriber.
     */
    public function __construct() {
        $this->removed = [];
        $this->updated = [];
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents() {
        return [
            Events::postPersist,
            Events::preRemove,
            Events::postUpdate,
            Events::postFlush,
        ];
    }

    /**
     * Queue up items to remove.
     */
    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->removed[] = $this->mapper->identify($entity);
        }
    }

    /**
     * Queue up items to index.
     */
    public function postPersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->updated[] = $this->mapper->toDocument($entity);
        }
    }

    /**
     * Queue up items to index.
     */
    public function postUpdate(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->updated[] = $this->mapper->toDocument($entity);
        }
    }

    /**
     * After the changes have been flushed to the ORM, they are also
     * flushed to the solr index.
     */
    public function postFlush(PostFlushEventArgs $args) : void {
        if (0 === count($this->removed) + count($this->updated)) {
            return;
        }
        $update = $this->client->createUpdate();

        foreach ($this->removed as $removed) {
            $update->addDeleteById($removed);
        }

        foreach ($this->updated as $updated) {
            $update->addDocument($updated);
        }
        $update->addCommit();
        $this->client->update($update);
    }

    /**
     * @required
     */
    public function setClient(Client $client) : void {
        $this->client = $client;
    }

    /**
     * @required
     */
    public function setEntityMapper(EntityMapper $mapper) : void {
        $this->mapper = $mapper;
    }
}
