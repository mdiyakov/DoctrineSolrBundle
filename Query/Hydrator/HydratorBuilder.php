<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Hydrator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Mdiyakov\DoctrineSolrBundle\Config\Config;

class HydratorBuilder
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Registry $registry
     * @param Config $config
     */
    public function __construct(Registry $registry, Config $config)
    {
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * @param string $entityClass
     * @return SelectQueryHydrator
     * @throws \LogicException
     */
    public function buildSelectQueryHydratorByClass($entityClass)
    {
        $em = $this->registry->getManagerForClass($entityClass);
        if (!$em instanceof EntityManager) {
            throw new \LogicException(
                'EntityManager must be instance of \Doctrine\ORM\EntityManager'
            );
        }

        return new SelectQueryHydrator(
            $em->getRepository($entityClass),
            $this->config->getSchemaByEntityClass($entityClass),
            $this->config->getEntityConfig($entityClass)
        );
    }

}