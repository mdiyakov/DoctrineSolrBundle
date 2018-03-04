<?php

namespace Mdiyakov\DoctrineSolrBundle\Filter;

use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\FilterConfigException;
use Mdiyakov\DoctrineSolrBundle\Exception\FilterException;
use Mdiyakov\DoctrineSolrBundle\Filter\Field\EntityFieldFilter;

class FilterValidator
{

    /**
     * @var EntityFilterInterface[]
     */
    private $filters = [];

    /**
     * @var string[][]
     */
    private $entityClassFilterMap = [];

    /**
     * @var string[][]
     */
    private $operatorFieldFilterConfigMap = [];

    /**
     * @var string[][]
     */
    private $serviceFilterConfigMap = [];

    /**
     * @param Config $config
     * @throws \Exception
     */
    public function __construct(Config $config)
    {
        $entitiesConfigs = $config->getIndexedEntities();

        foreach ($entitiesConfigs as $entityConfig) {
            if (array_key_exists('filters', $entityConfig)) {
                $this->entityClassFilterMap[$entityConfig['class']] = $entityConfig['filters'];
            }
        }

        $this->initFilters($config->getFilters());
    }

    /**
     * @param EntityFieldFilter $entityFieldFilter
     */
    public function addFieldFilter(EntityFieldFilter $entityFieldFilter)
    {
        $operator = $entityFieldFilter->getSupportedOperator();

        if (array_key_exists($operator, $this->operatorFieldFilterConfigMap) && is_array($this->operatorFieldFilterConfigMap[$operator])) {
            foreach($this->operatorFieldFilterConfigMap[$operator] as $filterName => $filterConfig) {
                $entityFieldFilterInstance = clone $entityFieldFilter;
                $entityFieldFilterInstance->setEntityFieldName($filterConfig['entity_field_name']);
                $entityFieldFilterInstance->setEntityFieldValue($filterConfig['entity_field_value']);

                $this->filters[$filterName] = $entityFieldFilterInstance;
            }
        }
    }

    /**
     * @param string $serviceId
     * @param EntityFilterInterface $entityFilter
     */
    public function addServiceEntityFilter($serviceId, EntityFilterInterface $entityFilter)
    {
        if (is_array($this->serviceFilterConfigMap[$serviceId])) {
            foreach($this->serviceFilterConfigMap[$serviceId] as $filterName => $filterConfig) {
                if ($serviceId == $filterConfig['service']) {
                    $this->filters[$filterName] = $entityFilter;
                }
            }
        }
    }


    /**
     * @param object $entity
     * @return bool
     * @throws FilterException
     * @throws \InvalidArgumentException
     */
    public function validate($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException('Argument must an object');
        }

        $entityClass = get_class($entity);
        if (array_key_exists($entityClass, $this->entityClassFilterMap) &&
            is_array($this->entityClassFilterMap[$entityClass])
        ) {
            $filtersNames = $this->entityClassFilterMap[$entityClass];
            foreach ($filtersNames as $filterName) {
                if (!array_key_exists($filterName, $this->filters)) {
                    throw new FilterConfigException(
                        sprintf(
                            'Filter "%s" is not initialized',
                            $filterName
                        )
                    );
                }

                $filter = $this->filters[$filterName];
                if (!$filter->isFilterValid($entity)) {
                    throw new FilterException($filter->getErrorMessage());
                }
            }
        }
    }

    /**
     * @param $filtersConfig
     */
    private function initFilters($filtersConfig)
    {
        if (array_key_exists('fields', $filtersConfig) && is_array($filtersConfig['fields'])) {
            foreach($filtersConfig['fields'] as $filterName => $filterConfig) {
                $filterOperator = $filterConfig['operator'];
                $this->operatorFieldFilterConfigMap[$filterOperator][$filterName] = $filterConfig;
            }
        }

        if (array_key_exists('services', $filtersConfig) && is_array($filtersConfig['services'])) {
            foreach($filtersConfig['services'] as $filterName => $filterConfig) {
                $serviceId = $filterConfig['service'];
                $this->serviceFilterConfigMap[$serviceId][$filterName] = $filterConfig;
            }

        }
    }
}