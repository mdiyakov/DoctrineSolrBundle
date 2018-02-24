<?php

namespace Mdiyakov\DoctrineSolrBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mdiyakov\DoctrineSolrBundle\Manager\IndexProcessManager;

class DoctrineEntityListener
{
    /**
     * @var IndexProcessManager
     */
    private $indexProcessManager;

    /**
     * @param IndexProcessManager $indexProcessManager
     */
    public function __construct(IndexProcessManager $indexProcessManager)
    {
        $this->indexProcessManager = $indexProcessManager;
    }

    /**
     * @param $entity
     * @param LifecycleEventArgs $event
     */
    public function postUpdate($entity, LifecycleEventArgs $event)
    {
        $this->reindexEntity($entity);
    }

    /**
     * @param $entity
     * @param LifecycleEventArgs $event
     */
    public function preRemove($entity, LifecycleEventArgs $event)
    {
        $this->removeEntity($entity);
    }

    /**
     * @param $entity
     * @param LifecycleEventArgs $event
     */
    public function postPersist($entity, LifecycleEventArgs $event)
    {
        $this->reindexEntity($entity);
    }

    /**
     * @param $entity
     */
    private function reindexEntity($entity)
    {
        $this->indexProcessManager->reindex($entity);
    }

    private function removeEntity($entity)
    {
        $this->indexProcessManager->remove($entity);
    }
}