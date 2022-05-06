<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace Nines\SolrBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Nines\SolrBundle\Exception\NotConfiguredException;
use Nines\SolrBundle\Services\SolrManager;
use ReflectionException;

/**
 * The index subscriber listens for changes to indexed entities. When the
 * changes are flused in the entity manager, they are also flushed to the solr
 * index.
 */
class IndexSubscriber implements EventSubscriber {
    private ?SolrManager $manager = null;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents() : array {
        return [
            Events::postPersist,
            Events::preRemove,
            Events::postUpdate,
            Events::postFlush,
        ];
    }

    /**
     * Queue up items to remove.
     *
     * @throws NotConfiguredException
     * @throws ReflectionException
     */
    public function preRemove(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();

        try {
            $this->manager->remove($entity);
        } catch (NotConfiguredException $e) {
            if ($this->manager->enabled()) {
                throw $e;
            }
        }
    }

    /**
     * Queue up items to index.
     *
     * @throws NotConfiguredException
     * @throws ReflectionException
     */
    public function postPersist(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();

        try {
            $this->manager->index($entity);
        } catch (NotConfiguredException $e) {
            if ($this->manager->enabled()) {
                throw $e;
            }
        }
    }

    /**
     * Queue up items to index.
     *
     * @throws NotConfiguredException
     * @throws ReflectionException
     */
    public function postUpdate(LifecycleEventArgs $args) : void {
        $entity = $args->getEntity();

        try {
            $this->manager->index($entity);
        } catch (NotConfiguredException $e) {
            if ($this->manager->enabled()) {
                throw $e;
            }
        }
    }

    /**
     * After the changes have been flushed to the ORM, they are also
     * flushed to the solr index.
     *
     * @throws NotConfiguredException
     */
    public function postFlush(PostFlushEventArgs $args) : void {
        try {
            $this->manager->flush();
        } catch (NotConfiguredException $e) {
            if ($this->manager->enabled()) {
                throw $e;
            }
        }
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
