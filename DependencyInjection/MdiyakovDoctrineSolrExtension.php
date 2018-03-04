<?php

namespace Mdiyakov\DoctrineSolrBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class MdiyakovDoctrineSolrExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('entity-field-filters.yml');

        if (!empty($config['indexed_entities'])) {
            $configDefinition = $container->getDefinition('mdiyakov_doctrine_solr.config.config');
            $configDefinition->replaceArgument(0, $config['indexed_entities']);
            $configDefinition->replaceArgument(1, $config['schemes']);
            $configDefinition->replaceArgument(2, $config['filters']);
            $configDefinition->replaceArgument(3, $config['solarium_clients']);

            $this->initializeDoctrineEntityListener($container, $config);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param $config
     */
    private function initializeDoctrineEntityListener(ContainerBuilder $container, $config)
    {
        $doctrineEntityListenerDefinition = $container->getDefinition('mdiyakov_doctrine_solr.event_listener.doctrine_entity');

        $indexedEntitiesClasses = array_map(
            function($entityConfig) {
                return $entityConfig['class'];
            },
            $config['indexed_entities']
        );

        foreach ($config['indexed_entities'] as $entityConfig) {

            $parents = class_parents($entityConfig['class']);
            if (array_intersect($indexedEntitiesClasses, $parents)) {
                continue;
            }

            $events = ['postUpdate', 'postPersist', 'preRemove'];
            foreach ($events as $event) {
                $doctrineEntityListenerDefinition->addTag(
                    'doctrine.orm.entity_listener',
                    [
                        'entity' => $entityConfig['class'],
                        'event' => $event
                    ]
                );
            }
        }
    }
}
