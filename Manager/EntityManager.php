<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;

class EntityManager
{
    /**
     * @var IndexProcessManager
     */
    private $indexProcessManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param IndexProcessManager $indexProcessManager
     * @param Registry $registry
     */
    public function __construct(
        IndexProcessManager $indexProcessManager,
        Registry $registry
    ) {
        $this->indexProcessManager = $indexProcessManager;
        $this->registry = $registry;
    }

    /**
     * @param string $entityManagerName
     * @param object[]|object $entity
     */
    public function flush($entityManagerName, $entity = null)
    {
        if (!is_object($entity) && !is_array($entity)) {
            throw new \InvalidArgumentException('Entity must be an object or array of objects');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->registry->getManager($entityManagerName);

        try {
            $em->flush($entity);
        } catch (\Exception $e) {
            if (!$em->isOpen()) {
                $this->registry->resetManager($entityManagerName);
            }
            if (!is_array($entity)) {
                $entity = [$entity];
            }

            foreach ($entity as $object) {
                if (!method_exists($object, 'getId')) {
                    throw new \LogicException('Entity must have method "getId" to handle rollback');
                }

                $object = $em->getRepository(get_class($object))->find($object->getId());
                if ($object) {
                    $this->indexProcessManager->reindex($object);
                } else {
                    $this->indexProcessManager->remove($object);
                }
            }
        }
    }
}