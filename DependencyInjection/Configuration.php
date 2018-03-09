<?php

namespace Mdiyakov\DoctrineSolrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mdiyakov_doctrine_solr');

        $rootNode
            ->children()
                ->arrayNode('indexed_entities')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                                ->scalarNode('class')->cannotBeEmpty()->end()
                                ->scalarNode('schema')->cannotBeEmpty()->end()
                                ->arrayNode('filters')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->scalarNode('finder_class')->end()
                                ->arrayNode('config')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('name')->end()
                                            ->scalarNode('value')->end()
                                        ->end()
                                    ->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('schemes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('client')->defaultValue(null)->end()
                            ->arrayNode('document_unique_field')
                                ->children()
                                    ->scalarNode('name')->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->arrayNode('config_entity_fields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('config_field_name')->cannotBeEmpty()->end()
                                        ->scalarNode('document_field_name')->cannotBeEmpty()->end()
                                        ->scalarNode('priority')->defaultValue(0)->end()
                                        ->booleanNode('discriminator')->defaultValue(false)->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('fields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('entity_field_name')->cannotBeEmpty()->end()
                                        ->scalarNode('document_field_name')->cannotBeEmpty()->end()
                                        ->enumNode('field_type')
                                            ->defaultValue('string')
                                            ->values(['string', 'array', 'boolean', 'int', 'double'])
                                        ->end()
                                        ->booleanNode('entity_primary_key')->defaultValue(false)->end()
                                        ->scalarNode('priority')->defaultValue(0)->end()
                                        ->scalarNode('suggester')->defaultValue(null)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->arrayNode('filters')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('fields')
                        ->defaultValue([])
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('entity_field_name')->cannotBeEmpty()->end()
                                ->scalarNode('entity_field_value')->cannotBeEmpty()->end()
                                ->scalarNode('operator')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('services')
                        ->defaultValue([])
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('service')->cannotBeEmpty()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('solarium_clients')
                ->defaultValue([])
                ->useAttributeAsKey('name')
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
