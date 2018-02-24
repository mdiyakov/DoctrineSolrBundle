<?php

namespace Mdiyakov\DoctrineSolrBundle\Command;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Mdiyakov\DoctrineSolrBundle\Config\Config;
use Mdiyakov\DoctrineSolrBundle\Manager\IndexProcessManager;

class IndexEntitiesCommand extends Command
{

    const BUNCH_COUNT = 100;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $possibleEntityTypes;

    /**
     * @var IndexProcessManager
     */
    private $indexProcessManager;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param Config $config
     * @param IndexProcessManager $indexProcessManager
     * @param EntityManager $em
     */
    public function __construct(Config $config, IndexProcessManager $indexProcessManager, EntityManager $em)
    {
        $this->config = $config;
        $this->indexProcessManager = $indexProcessManager;
        $this->possibleEntityTypes = array_keys($this->getAssocEntitiesClasses());
        $this->em = $em;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('mdiyakov:doctrine-solr:index')
            ->addArgument(
                'entity-type',
                InputArgument::OPTIONAL,
                'Specify type of entity to be indexed. Possible values are "all", "' . join('","', $this->possibleEntityTypes),
                'all'
            )
            ->addArgument(
                'id',
                InputArgument::OPTIONAL,
                'Specify id of entity to be indexed. Value must be integer. Also entity-type must be specify'
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
            foreach ($this->config->getIndexedClasses() as $entityClass) {
                $this->indexEntityClass($entityClass);
            }
        } else {
            $entitiesClasses = $this->getAssocEntitiesClasses();
            if (!array_key_exists($entityType, $entitiesClasses )) {
                throw new \Exception('There is no such possible entity-type. Check help section for possible values');
            }
            $entityClass = $entitiesClasses[$entityType];
            $this->indexEntityClass($entityClass, $entityId);
        }
    }

    /**
     * @param string $entityClass
     * @param int|null $id
     * @throws \Exception
     */
    private function indexEntityClass($entityClass, $id = null)
    {
        $repository = $this->em->getRepository($entityClass);
        if ($id) {
            $entity = $repository->find($id);
            if (!$entity) {
                throw new \Exception(
                    sprintf('There is no %s with id %s in database', $entityClass, $id)
                );
            }
            $this->processEntity($entity);
        } else {
            $this->processRepository($repository);
        }
    }

    /**
     * @param EntityRepository $repository
     */
    private function processRepository(EntityRepository $repository)
    {
        $offset = 0;
        while ($entities = $repository->findBy([],['id'=> 'asc'], self::BUNCH_COUNT, $offset)) {
            foreach ($entities as $entity) {
                $this->processEntity($entity);
            }
            $this->em->clear($repository->getClassName());
            $offset += self::BUNCH_COUNT;
        }
    }

    /**
     * @param $entity
     */
    private function processEntity($entity)
    {
        $result = $this->indexProcessManager->reindex($entity);
        $status = $result->isSuccess() ? 'successfully' : 'failed. Error ' . $result->getError();
        $message = sprintf('Indexing of %s with id %s is %s',
            get_class($entity),
            $entity->getId(),
            $status
        );
        $this->output->writeln($message);
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
}