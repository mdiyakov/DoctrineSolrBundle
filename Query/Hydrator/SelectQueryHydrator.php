<?php

namespace Mdiyakov\DoctrineSolrBundle\Query\Hydrator;

use Doctrine\ORM\EntityRepository;
use Mdiyakov\DoctrineSolrBundle\Exception\DiscriminatorFieldException;
use Mdiyakov\DoctrineSolrBundle\Exception\EntityNotExistInDatabaseException;
use Mdiyakov\DoctrineSolrBundle\Exception\HydratorException;
use Mdiyakov\DoctrineSolrBundle\Schema\Schema;

class SelectQueryHydrator
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $entityRepository;

    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @param EntityRepository $repository
     * @param Schema $schema
     * @param array $entityConfig
     */
    public function __construct(EntityRepository $repository, Schema $schema, $entityConfig)
    {
        $this->schema = $schema;
        $this->entityRepository = $repository;
        $this->entityConfig = $entityConfig;
    }

    /**
     * @param array $documentsArray
     * @return object[]
     * @throws DiscriminatorFieldException
     * @throws EntityNotExistInDatabaseException
     * @thorws HydratorException
     */
    public function hydrate($documentsArray)
    {
        if (!is_array($documentsArray)) {
            throw new HydratorException('Result dataset is empty');
        }

        $scoreStatements = [];
        $discriminatorField = $this->schema->getDiscriminatorConfigField();
        $entityPrimaryKeyField = $this->schema->getEntityPrimaryKeyField();
        $primaryKeyValues = [];
        $orderValue = count($documentsArray);
        foreach($documentsArray as $documentRow) {
            if (!array_key_exists($discriminatorField->getConfigFieldName(), $documentRow)) {
                throw new DiscriminatorFieldException(
                    sprintf('Discriminator field "%s" must be presented in dataset', $discriminatorField->getConfigFieldName())
                );
            }

            if ($discriminatorField->getValue($this->entityConfig) !== $documentRow[$discriminatorField->getDocumentFieldName()]) {
                throw new DiscriminatorFieldException(
                    sprintf(
                        'Discriminator field value "%s" must be %s but %s is provided',
                        $discriminatorField->getConfigFieldName(),
                        $this->entityConfig[$discriminatorField->getConfigFieldName()],
                        $documentRow[$discriminatorField->getDocumentFieldName()]
                    )
                );
            }

            $primaryKeyValues[] = $documentRow[$entityPrimaryKeyField->getDocumentFieldName()];
            $scoreStatements[] = sprintf(
                'WHEN entity.%s = %s THEN %s',
                $entityPrimaryKeyField->getEntityFieldName(),
                $documentRow[$entityPrimaryKeyField->getDocumentFieldName()],
                $orderValue
            );
            $orderValue = --$orderValue;
        }

        $entities = $this->entityRepository
            ->createQueryBuilder('entity')
            ->addSelect(sprintf('(CASE %s ELSE 0 END) AS HIDDEN score', join(' ' ,$scoreStatements)))
            ->where(
                sprintf('entity.%s in (:ids)', $entityPrimaryKeyField->getEntityFieldName())
            )
            ->setParameter(':ids', $primaryKeyValues)
            ->addOrderBy('score', 'desc')
            ->getQuery()->getResult()
        ;

        if (count($entities) != count($documentsArray)) {
            foreach($entities as $entity) {
                $entityPrimaryKeyValue = $entityPrimaryKeyField->getEntityFieldValue($entity);
                if (array_search($entityPrimaryKeyValue, $primaryKeyValues) === false) {
                    throw new HydratorException(
                        sprintf(
                            'Entity %s with %s primary key is not found in database',
                            $this->entityConfig['class'],
                            $entityPrimaryKeyValue
                        )
                    );
                }
            }
        }

        return $entities;
    }
}