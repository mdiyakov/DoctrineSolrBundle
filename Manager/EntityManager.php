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
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \Exception
     */
    public function flush($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Entity must be an object');
        }

        if (!method_exists($entity, 'getId')) {
            throw new \LogicException('Entity must have method "getId" to handle rollback');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getEm($entity);
        try {
            $em->persist($entity);
            $em->flush($entity);
        } catch (\Exception $e) {
            if (!$em->isOpen()) {
                $em = $em->create(
                    $em->getConnection(), $em->getConfiguration()
                );
            }

            $previousEntity = $em->getRepository(get_class($entity))->find($entity->getId());
            if ($previousEntity) {
                $this->indexProcessManager->reindex($previousEntity);
            } else {
                $this->indexProcessManager->remove($entity);
            }

            throw $e;
        }
    }

    /**
     * @param object $entity
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