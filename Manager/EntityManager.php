<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;

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
     * @param object $entity
     * @throws \InvalidArgumentException
     */
    public function persist($entity)
    {
        $this->getEm($entity)->persist($entity);
    }

    /**
     * @param object[]|object $entity
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function flush($entity = null)
    {
        if (!is_object($entity) && !is_array($entity)) {
            throw new \InvalidArgumentException('Entity must be an object or array of objects');
        }

        if (!is_array($entity)) {
            $entity = [$entity];
        }

        foreach ($entity as $object) {
            if (!method_exists($object, 'getId')) {
                throw new \LogicException('Entity must have method "getId" to handle rollback');
            }

            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->getEm($object);
            try {
                $em->flush($object);
            } catch (\Exception $e) {
                if (!$em->isOpen()) {
                    $em = $em->create(
                        $em->getConnection(), $em->getConfiguration()
                    );
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

    /**
     * @param $entity
     * @throws \InvalidArgumentException
     * @return EntityManagerInterface
     */
    private function getEm($entity)
    {
        $em = $this->registry->getManagerForClass(get_class($entity));
        if (!$em) {
            throw new \InvalidArgumentException(
                sprintf('There is no entity manager for "%s" class', get_class($entity))
            );
        }

        if (!$em instanceof EntityManagerInterface) {
            throw new \InvalidArgumentException(
                'Entity manager must be instance of  "EntityManagerInterface" class'
            );
        }

        return $em;
    }
}