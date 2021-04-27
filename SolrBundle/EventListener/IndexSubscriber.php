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
use Nines\SolrBundle\Services\SolrManager;

/**
 * The index subscriber listens for changes to indexed entities. When the
 * changes are flused in the entity manager, they are also flushed to the solr
 * index.
 */
class IndexSubscriber implements EventSubscriber {
    private SolrManager $manager;

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
        $this->manager->remove($entity);
    }

    /**
     * Queue up items to index.
     */
    public function postPersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        $this->manager->index($entity);
    }

    /**
     * Queue up items to index.
     */
    public function postUpdate(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();
        $this->manager->index($entity);
    }

    /**
     * After the changes have been flushed to the ORM, they are also
     * flushed to the solr index.
     */
    public function postFlush(PostFlushEventArgs $args) : void {
        $this->manager->flush();
    }

    /**
     * @required
     */
    public function setSolrManager(SolrManager $manager) : void {
        $this->manager = $manager;
    }
}
