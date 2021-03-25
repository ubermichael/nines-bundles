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

class IndexSubscriber implements EventSubscriber
{
    private $removed;

    private $updated;

    /**
     * @var EntityMapper
     */
    private $mapper;

    private Client $client;

    public function __construct() {
        $this->removed = [];
        $this->updated = [];
    }

    public function getSubscribedEvents() {
        return [
            Events::postPersist,
            Events::preRemove,
            Events::postUpdate,
            Events::postFlush,
        ];
    }

    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->removed[] = $this->mapper->identify($entity);
        }
    }

    public function postPersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->updated[] = $this->mapper->toDocument($entity);
        }
    }

    public function postUpdate(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        if ($this->mapper->isMapped($entity)) {
            $this->updated[] = $this->mapper->toDocument($entity);
        }
    }

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
