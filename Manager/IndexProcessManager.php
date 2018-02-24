<?php

namespace Mdiyakov\DoctrineSolrBundle\Manager;

use Mdiyakov\DoctrineSolrBundle\Exception\FilterException;
use Mdiyakov\DoctrineSolrBundle\Filter\FilterValidator;
use Mdiyakov\DoctrineSolrBundle\Indexer\IndexerBuilder;

class IndexProcessManager
{
    /**
     * @var IndexerBuilder
     */
    private $indexerBuilder;

    /**
     * @var FilterValidator
     */
    private $filterValidator;

    /**
     * @param IndexerBuilder $indexerBuilder
     * @param FilterValidator $filterValidator
     */
    public function __construct(IndexerBuilder $indexerBuilder, FilterValidator $filterValidator)
    {
        $this->indexerBuilder = $indexerBuilder;
        $this->filterValidator = $filterValidator;
    }

    /**
     * @param object $entity
     * @return IndexProcessResult
     * @throws \InvalidArgumentException
     */
    public function reindex($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Argument must be an entity');
        }

        $result = new IndexProcessResult();
        $entityClass = get_class($entity);
        $indexer = $this->indexerBuilder->createByEntityClass($entityClass);
        try {
            $this->filterValidator->validate($entity);
            $indexer->indexAllFields($entity);
            $result->setSuccess(true);
        } catch (FilterException $e) {
            $indexer->removeByPrimaryKey($entity);
            $result->setError($e->getMessage());
        }

        return $result;
    }

    /**
     * @param object $entity
     */
    public function remove($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Argument must be an entity');
        }

        $entityClass = get_class($entity);
        $indexer = $this->indexerBuilder->createByEntityClass($entityClass);
        $indexer->removeByPrimaryKey($entity);
    }
}