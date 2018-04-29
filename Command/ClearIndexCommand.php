<?php

namespace Mdiyakov\DoctrineSolrBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Exception\EntityNotIndexedException;
use Mdiyakov\DoctrineSolrBundle\Query\UpdateQueryBuilder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ClearIndexCommand extends Command
{

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UpdateQueryBuilder
     */
    private $updateQueryBuilder;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param Config $config
     * @param UpdateQueryBuilder $updateQueryBuilder
     * @param EntityManager $em
     */
    public function __construct(
        Config $config,
        UpdateQueryBuilder $updateQueryBuilder,
        EntityManager $em
    )
    {
        $this->config = $config;
        $this->possibleEntityTypes = array_keys($this->getAssocEntitiesClasses());
        $this->updateQueryBuilder = $updateQueryBuilder;
        $this->em = $em;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('doctrine-solr:clear-index')
            ->addArgument(
                'entity-type',
                InputArgument::OPTIONAL,
                'Specify type of entity to be removed. Possible values are "all", "' . join('","', $this->possibleEntityTypes),
                'all'
            )
            ->addArgument(
                'id',
                InputArgument::OPTIONAL,
                'Specify id of entity to be removed. Value must be integer. Also entity-type must be specify'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $entityType = $input->getArgument('entity-type');
        $entityId = (int) $input->getArgument('id');

        if ($entityType == 'all') {
            foreach ($this->config->getIndexedEntities() as $entityConfig) {
                $this->deleteByEntityConfig(
                    $entityConfig
                );
            }
        } else {
            $entitiesClasses = $this->getAssocEntitiesClasses();
            if (!array_key_exists($entityType, $entitiesClasses )) {
                throw new \Exception('There is no such possible entity-type. Check help section for possible values');
            }
            $entityClass = $entitiesClasses[$entityType];
            $entityConfig = $this->config->getEntityConfig($entityClass);
            if (!$entityConfig) {
                throw new EntityNotIndexedException(
                    sprintf('Entity class %s is not found in config', $entityClass)
                );
            }

            $this->deleteByEntityConfig(
                $entityConfig,
                $entityId
            );
        }
    }

    /**
     * @return string[]
     */
    private function getAssocEntitiesClasses()
    {
        $entitiesConfigs = $this->config->getIndexedEntities();

        $result = [];
        foreach ($entitiesConfigs as $entityKey => $entityConfig) {
            $result[$entityKey] = $entityConfig['class'];
        }

        return $result;
    }

    /**
     * @param string[][] $entityConfig
     * @param null|int $id
     * @throws EntityNotFoundException
     */
    private function deleteByEntityConfig($entityConfig, $id = null)
    {
        $schemaName = $entityConfig['schema'];
        $updateQuery = $this->updateQueryBuilder->buildUpdateQueryBySchemaName($schemaName);
        $schema = $this->config->getSchemaByName($schemaName);
        $discriminatorField = $schema->getDiscriminatorConfigField();
        $discriminatorValue = $discriminatorField->getValue($entityConfig);

        if (empty($id)) {
            $updateQuery->addDeleteCriteriaByField(
                $discriminatorField->getDocumentFieldName(),
                $discriminatorValue
            );
            $message = sprintf('Removing of %s is completed successfully',
                $entityConfig['class']
            );

        } else {
            $updateQuery->addDeleteCriteriaByUniqueFieldValue(
                sprintf('%s-%s', $discriminatorValue, $id)
            );
            $message = sprintf('Removing of %s with id %s is completed successfully',
                $entityConfig['class'],
                $id
            );

        }

        $updateQuery->update();
        $this->output->writeln($message);
    }
}