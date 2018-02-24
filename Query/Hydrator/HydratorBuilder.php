<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Hydrator;

use Doctrine\ORM\EntityManager;
use Mdiyakov\DoctrineSolrBundle\Config\Config;

class HydratorBuilder
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param EntityManager $em
     * @param Config $config
     */
    public function __construct(EntityManager $em, Config $config)
    {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * @param string $entityClass
     * @return SelectQueryHydrator
     */
    public function buildSelectQueryHydratorByClass($entityClass)
    {
        return new SelectQueryHydrator(
            $this->em->getRepository($entityClass),
            $this->config->getSchemaByEntityClass($entityClass),
            $this->config->getEntityConfig($entityClass)
        );
    }

}